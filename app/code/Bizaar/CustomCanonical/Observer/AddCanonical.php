<?php

namespace Bizaar\CustomCanonical\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\App\Request\Http;
use Magento\Framework\UrlInterface;

class AddCanonical implements ObserverInterface
{
    /**
     * @var PageConfig
     */
    protected $pageConfig;
    protected $urlBuilder;

    /**
     * @param PageConfig $pageConfig
     */
    public function __construct(
        PageConfig $pageConfig,
        Http $request,
        UrlInterface $urlBuilder
        )
    {
        $this->pageConfig = $pageConfig;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
    }

    public function execute(Observer $observer)
    {
        $baseUrl = $this->urlBuilder->getBaseUrl();
        $canonicalUrl = $baseUrl.ltrim($this->request->getOriginalPathInfo(), '/'); // Replace with your custom URL logic
        $this->pageConfig->addRemotePageAsset(
            $canonicalUrl,
            'link_rel',
            ['attributes' => ['rel' => 'canonical']]
        );
    }
}
