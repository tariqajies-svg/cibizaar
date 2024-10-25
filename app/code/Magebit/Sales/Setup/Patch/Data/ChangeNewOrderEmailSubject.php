<?php
/**
 * This file is part of the Magebit_Sales package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Sales
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Sales\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\ResourceModel\Template as TemplateResource;
use Magento\Email\Model\Template;

/**
 * Change new order email template subject text
 */
class ChangeNewOrderEmailSubject implements DataPatchInterface
{
    private const SUBJECT_TEXT = '{{trans "Your %store_name order confirmation" store_name=$store.frontend_name}}' .
        ' - {{var order.increment_id}}';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory    $templateCollectionFactory
     * @param TemplateResource     $templateResourceModel
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly CollectionFactory $templateCollectionFactory,
        private readonly TemplateResource $templateResourceModel
    ) {
    }

    /**
     * Change new order email template subject
     *
     * @return void
     */
    public function apply(): void
    {
        $templateId = $this->scopeConfig->getValue(OrderIdentity::XML_PATH_EMAIL_TEMPLATE);

        /**
         * @var Template $template
         */
        $template = $this->templateCollectionFactory->create()->addFieldToFilter(
            'template_id',
            $templateId
        )->getFirstItem();

        if ($template->getTemplateId()) {
            $template->setTemplateSubject(self::SUBJECT_TEXT);

            $this->templateResourceModel->save($template);
        }
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
