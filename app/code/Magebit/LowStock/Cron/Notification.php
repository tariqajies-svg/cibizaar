<?php
/**
 * This file is part of the Magebit_LowStock package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_LowStock
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\LowStock\Cron;

use Magebit\LowStock\Api\Data\LowStockNotificationInterface;
use Magebit\LowStock\Api\Data\LowStockNotificationInterfaceFactory;
use Magebit\LowStock\Api\LowStockNotificationRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\LowQuantityCollection;
use Magento\ProductAlert\Model\Email;
use Magento\Store\Model\ScopeInterface;

/**
 * Checks what products are in low stock according to settings
 * and send out email if possible
 */
class Notification
{
    private const EMAIL_PATH = 'low_stock/notifications/email';
    private const IS_ENABLED_PATH = 'low_stock/notifications/enable';
    private const EMAIL_TEMPLATE = 'low_stock/notifications/template';

    /**
     * @var LowStockNotificationRepositoryInterface
     */
    private LowStockNotificationRepositoryInterface $lowStockNotificationRepository;
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var LowQuantityCollection
     */
    private LowQuantityCollection $lowQuantityCollection;
    /**
     * @var LowStockNotificationInterfaceFactory
     */
    private LowStockNotificationInterfaceFactory $lowStockNotificationInterfaceFactory;
    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * Notification constructor
     *
     * @param LowStockNotificationRepositoryInterface $lowStockNotificationRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param LowQuantityCollection $lowQuantityCollection
     * @param LowStockNotificationInterfaceFactory $lowStockNotificationInterfaceFactory
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        LowStockNotificationRepositoryInterface $lowStockNotificationRepository,
        SearchCriteriaBuilder                   $searchCriteriaBuilder,
        ScopeConfigInterface                    $scopeConfig,
        LowQuantityCollection                   $lowQuantityCollection,
        LowStockNotificationInterfaceFactory    $lowStockNotificationInterfaceFactory,
        TransportBuilder                        $transportBuilder
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->lowStockNotificationInterfaceFactory = $lowStockNotificationInterfaceFactory;
        $this->lowQuantityCollection = $lowQuantityCollection;
        $this->lowStockNotificationRepository = $lowStockNotificationRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Find new low stock products and send email
     *
     * @return void
     * @throws LocalizedException
     * @throws MailException
     */
    public function execute(): void
    {
        if (!$this->scopeConfig->getValue(self::IS_ENABLED_PATH) ||
            !$this->scopeConfig->getValue(self::EMAIL_PATH)) {
            return;
        }

        $notifiedProducts = $this->getAlreadyNotifiedProducts();

        $lowStockProductsCollection = $this->lowQuantityCollection->getItems();

        $lowStockProducts = [];
        $productsToNotify = [];
        foreach ($lowStockProductsCollection as $item) {
            $lowStockProducts[$item->getSku()] = [
                'name' => $item->getProductName(),
                'sku' => $item->getSku(),
                'qty' => $item->getQuantity()
            ];
            $toNotify = true;
            foreach ($notifiedProducts as $notifiedProduct) {
                if ($notifiedProduct->getSku() === $item->getSku()) {
                    $toNotify = false;
                }
            }
            if ($toNotify) {
                $productsToNotify[] = $lowStockProducts[$item->getSku()];
            }
        }

        $this->deleteOldNotifications(array_keys($lowStockProducts));
        $this->addNewNotifications($productsToNotify);

        $this->sendEmail($productsToNotify);
    }

    /**
     * Get previously notified products
     *
     * @return LowStockNotificationInterface[]
     */
    private function getAlreadyNotifiedProducts(): array
    {
        return $this->lowStockNotificationRepository->getList(
            $this->searchCriteriaBuilder->create()
        );
    }

    /**
     * Deletes already notified products that have qty "fixed"
     *
     * @param array $productSkus
     * @return void
     */
    private function deleteOldNotifications(array $productSkus): void
    {
        $items = $this->lowStockNotificationRepository->getList(
            $this->searchCriteriaBuilder->addFilter(LowStockNotificationInterface::SKU, $productSkus, 'nin')
                ->create()
        );

        foreach ($items as $item) {
            $this->lowStockNotificationRepository->delete($item);
        }
    }

    /**
     * Sets products that will be notified to not send out duplicate emails every hour
     *
     * @param array $productsToNotify
     * @return void
     */
    private function addNewNotifications(array $productsToNotify): void
    {
        foreach ($productsToNotify as $item) {
            /** @var LowStockNotificationInterface $notification */
            $notification = $this->lowStockNotificationInterfaceFactory->create();
            $notification->setSku($item['sku']);
            $this->lowStockNotificationRepository->save($notification);
        }
    }

    /**
     * Send out low stock notification email
     *
     * @param array $productsToNotify
     * @return void
     * @throws LocalizedException
     * @throws MailException
     */
    private function sendEmail(array $productsToNotify): void
    {
        $template = $this->scopeConfig->getValue(self::EMAIL_TEMPLATE);
        $email = $this->scopeConfig->getValue(self::EMAIL_PATH);

        if (!$email || empty($productsToNotify)) {
            return;
        }

        $transport = $this->transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions([
            'area' => Area::AREA_FRONTEND,
            'store' => 0
        ])->setTemplateVars([
            'items' => $productsToNotify
        ])->setFromByScope(
            $this->scopeConfig->getValue(
                Email::XML_PATH_EMAIL_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                0
            ),
            0
        )->addTo($email)
            ->getTransport();

        $transport->sendMessage();
    }
}
