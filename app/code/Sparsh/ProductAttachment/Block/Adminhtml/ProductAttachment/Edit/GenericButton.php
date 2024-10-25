<?php
/**
 * Sparsh ProductAttachment
 * PHP version 8.2
 * 
 * @category Sparsh
 * @package  Sparsh_ProductAttachment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\ProductAttachment\Block\Adminhtml\ProductAttachment\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 *
 * @category Sparsh
 * @package  Sparsh_ProductAttachment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class GenericButton
{
    /**
     * Context
     *
     * @var Context
     */
    protected $context;
    /**
     * GenericButton constructor
     *
     * @param Context $context Context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * Return Post ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->context->getRequest()->getParam('id');
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route  Route
     * @param array  $params Params
     *
     * @return string url for the button
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
