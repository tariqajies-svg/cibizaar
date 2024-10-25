<?php
namespace Bizaar\Meta\Block\Category;

use Magento\Catalog\Block\Category\View as CategoryView;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Framework\App\Request\Http;
use Magento\Cms\Model\BlockFactory;

class View extends CategoryView
{
    protected $registry;
    public $customArgument;
    protected $customData;
    protected $blockFactory;
    protected $customMetaTitle;
    protected $customMetaDescription;
    protected $customMetaKeywords;

    public function __construct(
        Context $context,
        Resolver $layerResolver,
        Registry $registry,
        CategoryHelper $categoryHelper,
        Http $request,
        BlockFactory $blockFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->blockFactory = $blockFactory;
        parent::__construct(
            $context,
            $layerResolver,
            $registry,
            $categoryHelper,
            $data
        );
    }

    /**
     * Example method to set meta title data
     */
    public function setCustomMetaTitle($data)
    {
        $this->customMetaTitle = $data;
    }

    /**
     * Example method to get meta title data
     */
    public function getCustomMetaTitle()
    {
        return $this->customMetaTitle;
    }

    /**
     * Example method to set meta description data
     */
    public function setCustomMetaDescription($data)
    {
        $this->customMetaDescription = $data;
    }

    /**
     * Example method to get meta description data
     */
    public function getCustomMetaDescription()
    {
        return $this->customMetaDescription;
    }

    /**
     * Example method to set meta keywords data
     */
    public function setCustomMetaKeywords($data)
    {
        $this->customMetaKeywords = $data;
    }

    /**
     * Example method to get meta keywords data
     */
    public function getCustomMetaKeywords()
    {
        return $this->customMetaKeywords;
    }

    /**
     * Method to perform some operation and store result in a class property
     */
    public function performOperation()
    {
        $category = $this->getCurrentCategory();
        $customCategoryMetaBlock = "meta".strtolower(str_replace('/', '-', $this->request->getOriginalPathInfo()));
        $block = $this->blockFactory->create()->load($customCategoryMetaBlock);
        $customCategoryMetaBlockData = $block->getContent();
        
        if( !empty($customCategoryMetaBlockData) && ($customCategoryMetaBlockData!==""))
        {
            $metaSeparate = explode("|||",html_entity_decode(strip_tags($customCategoryMetaBlockData), ENT_QUOTES | ENT_HTML5));

            if( (count($metaSeparate)>0))
            {
                $metaTitle = $metaSeparate[1];
                $metaDescription = $metaSeparate[2];
                $metaKeywords = $metaSeparate[3];

                $resultTitle = $metaTitle;
                $resultDescription = $metaDescription;
                $resultKeywords = $metaKeywords;

                $this->setCustomMetaTitle($resultTitle);
                $this->setCustomMetaDescription($resultDescription);
                $this->setCustomMetaKeywords($resultKeywords);
            }
        }
    }

    public function metaTitleResult()
    {
        // Get the result from the class property
        $result = $this->getCustomMetaTitle();

        // Perform some operation with the result
        return $result;
    }

    public function metaDescriptionResult()
    {
        // Get the result from the class property
        $result = $this->getCustomMetaDescription();

        // Perform some operation with the result
        return $result;
    }

    public function metaKeywordsResult()
    {
        // Get the result from the class property
        $result = $this->getCustomMetaKeywords();

        // Perform some operation with the result
        return $result;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        // Call the first method to perform the operation
        $this->performOperation();

        $titleResult = $this->metaTitleResult();
        $descriptionResult = $this->metaDescriptionResult();
        $keywordsResult = $this->metaKeywordsResult();

        // You can now use $operationResult as needed
        $this->pageConfig->getTitle()->set($titleResult);
        $this->pageConfig->setDescription($descriptionResult);
        $this->pageConfig->setKeywords($keywordsResult);

        return $this;
    }
}
