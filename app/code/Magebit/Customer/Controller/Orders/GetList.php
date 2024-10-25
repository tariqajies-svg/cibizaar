<?php
/**
 * This file is part of the Magebit_Customer package.
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

namespace Magebit\Customer\Controller\Orders;

use Exception;
use Magebit\Customer\Service\OrdersFiltersService;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class GetList implements HttpPostActionInterface
{
    /**
     * @var ResultFactory
     */
    private ResultFactory $resultFactory;

    /**
     * @var Session
     */
    private Session $customerSession;

    /**
     * @var Http
     */
    private Http $request;

    /**
     * @var OrdersFiltersService
     */
    private OrdersFiltersService $filtersService;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param Http $request
     * @param OrdersFiltersService $filtersService
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Http $request,
        OrdersFiltersService $filtersService
    ) {
        $this->resultFactory = $context->getResultFactory();
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->filtersService = $filtersService;
    }

    /**
     * Return orders with filters for current user
     *
     * @return Json
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute(): Json
    {
        if (!$this->customerSession->isLoggedIn()) {
            /** @var Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

            return $resultJson->setData([]);
        }

        $customer = $this->customerSession->getCustomerData();
        $customerId = $this->filtersService->getCustomerId($customer);

        $data = json_decode($this->request->getContent(), false, 20, JSON_THROW_ON_ERROR);
        $params = $this->filtersService->addTypeAndSearchPropertyToParams($data->data);
        $searchQuery = $data->searchQuery;
        $result = ['orders' => []];

        if ($searchQuery) {
            $searchWords = $this->filtersService->createSearchParams($searchQuery);
            $params = $this->filtersService->compareParamWithWords($params, $searchWords, $customerId);
        }

        if (!isset($params->nullableCollection)) {
            if ($collection = $this->filtersService->getFilteredCollection($params, $customerId)) {
                $collection->load();
                $result['orders'] = $this->filtersService->makeCollectionDataArray($collection);
            }
        } else {
            unset($params->nullableCollection);
        }
        $result['params'] = $this->filtersService->paramsForFE($params);

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($result);
    }
}
