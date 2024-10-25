<?php
/**
 * This file is part of the Magebit_TwoFa package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_TwoFa
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\TwoFa\Controller\Auth;

use Exception;
use Magebit\TwoFa\Service\TwoFaService;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class SelectMethod implements HttpPostActionInterface
{
    /**
     * @param TwoFaService $twoFaService
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        private readonly TwoFaService $twoFaService,
        private readonly RequestInterface $request,
        private readonly ResultFactory $resultFactory
    ) {
    }

    /**
     * @return ResponseInterface|Json|(Json&ResultInterface)|ResultInterface
     */
    public function execute()
    {
        $method = $this->request->getParam('method');
        $email = $this->request->getParam('email');

        try {
            $this->twoFaService->processMethod($method, $email);
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'success' => true
            ]);
        } catch (Exception $exception) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
