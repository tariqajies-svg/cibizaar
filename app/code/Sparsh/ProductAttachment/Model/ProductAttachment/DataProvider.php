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
namespace Sparsh\ProductAttachment\Model\ProductAttachment;

use Sparsh\ProductAttachment\Model\ResourceModel\ProductAttachment\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 *
 * @category Sparsh
 * @package  Sparsh_ProductAttachment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Collection
     *
     * @var \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    protected $collection;

    /**
     * Data Persistor
     *
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Loaddata
     *
     * @var array
     */
    protected $loadedData;

    /**
     * Storemanager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * Constructor
     *
     * @param string                 $name
     * @param string                 $primaryFieldName
     * @param string                 $requestFieldName
     * @param CollectionFactory      $productattachmentCollectionFactory
     * @param StoreManagerInterface  $storeManager
     * @param DataPersistorInterface $dataPersistor
     * @param array                  $meta
     * @param array                  $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $productattachmentCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $productattachmentCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager=$storeManager;
        
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();

        foreach ($items as $block) {
            $this->loadedData[$block->getId()] = $block->getData();

            if ($block->getAttachFile()) {
                $m['attach_file'][0]['name'] = $block->getAttachFile();
                $m['attach_file'][0]['url'] = $this->getMediaUrl().$block->getAttachFile();
                $fullData = $this->loadedData;
                $this->loadedData[$block->getId()] = array_merge($fullData[$block->getId()], $m);
            }
        }

        $data = $this->dataPersistor->get('productattachment');
        if (!empty($data)) {
            $block = $this->collection->getNewEmptyItem();
            $block->setData($data);
            $this->loadedData[$block->getId()] = $block->getData();
            $this->dataPersistor->clear('productattachment');
        }

        return $this->loadedData;
    }

    /**
     * Return media path for attachment module
     *
     * @return string
     */
    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'sparsh/product_attachment/';
        return $mediaUrl;
    }
}
