<?php
/**
 * This file is part of the Magebit_HyvaRequisitionLists package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */
declare(strict_types=1);

namespace Magebit\HyvaRequisitionLists\Plugin\Controller;

use Aheadworks\RequisitionLists\Api\RequisitionListRepositoryInterface;
use Aheadworks\RequisitionLists\Controller\RList\Add as AddOrigin;
use Aheadworks\RequisitionLists\Model\Message\MessageManager;
use Aheadworks\RequisitionLists\Model\RequisitionList\Manager as ListManager;
use Aheadworks\RequisitionLists\Model\RequisitionList\Provider;
use Aheadworks\RequisitionLists\Model\Url;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Add extends AddOrigin
{
    /**
     * @var ListManager
     */
    private ListManager $manager;

    /**
     * @var MessageManager
     */
    private MessageManager $messager;

    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @param MessageManager $messager
     * @param Provider $provider
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param ResponseFactory $responseFactory
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @param ListManager $manager
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param PageFactory $pageFactory
     * @param Escaper $escaper
     */
    public function __construct(
        MessageManager $messager,
        Provider $provider,
        Context $context,
        CustomerSession $customerSession,
        ResponseFactory $responseFactory,
        RequisitionListRepositoryInterface $requisitionListRepository,
        ListManager $manager,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        PageFactory $pageFactory,
        Escaper $escaper
    ) {
        parent::__construct(
            $messager,
            $provider,
            $context,
            $customerSession,
            $responseFactory,
            $requisitionListRepository,
            $manager,
            $registry,
            $productRepository,
            $pageFactory
        );
        $this->manager = $manager;
        $this->registry = $registry;
        $this->messager = $messager;
        $this->productRepository = $productRepository;
        $this->escaper = $escaper;
    }

    /**
     * @param AddOrigin $subject
     * @param callable $proceed
     * @return Redirect|ResultInterface
     */
    public function aroundExecute(AddOrigin $subject, callable $proceed) // No return type because throwing 500
    {
        $isAjax = $this->getRequest()->isAjax();
        $resultData = [
            'success' => false
        ];
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $product = $subject->initProduct();
            if ($product) {
                $item = $this->manager->addItemToListFromProductPage($subject->getRequest(), $product);
                $data = $subject->resolveListData($item);
                $this->messager->addCombineSuccessMessage(
                    $data
                );
                $resultRedirect->setRefererUrl();
                $resultData['success'] = true;
            }
        } catch (\Exception $e) {
            $url = $this->registry->registry('list-url-redirect');

            if ($url) {
                $this->messageManager->addNoticeMessage(
                    $this->escaper->escapeHtml($e->getMessage())
                );

                return $resultRedirect->setPath($url);
            } else {
                $resultRedirect->setPath(Url::REQUISITION_LIST_ROUTE);
                $this->messageManager->addErrorMessage(
                    __('Something wen\'t wrong while adding product to Requisition List.')
                );
            }
        }

        if ($isAjax) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($resultData);
        }

        return $resultRedirect;
    }
}
