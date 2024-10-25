<?php
/**
 * This file is part of the Magebit_HyvaTheme package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\HyvaTheme\Controller\Menu;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Index implements HttpGetActionInterface
{
    public const AJAX_MENU_UNIQUE_ID = 'ajax_menu_unique_id';

    /**
     * Constructor
     *
     * @param ResultFactory $resultFactory
     * @param Http $request
     */
    public function __construct(
        protected readonly ResultFactory $resultFactory,
        protected readonly Http $request
    ) {
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $layout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);

        if ($ajaxMenuUniqueId = $this->request->getParam(self::AJAX_MENU_UNIQUE_ID)) {
            $layout->getLayout()->getBlock('topmenu_generic.wrapper')->setData(
                self::AJAX_MENU_UNIQUE_ID,
                $ajaxMenuUniqueId
            );
        }

        return $layout;
    }
}
