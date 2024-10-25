<?php
namespace Ktpl\HidePrice\Block\Adminhtml\Request\Edit;

class Info extends \Amasty\HidePrice\Block\Adminhtml\Request\Edit\Info
{
    protected $currentRequest;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\HidePrice\Model\Source\Status
     */
    private $status;

    /**
     * Info constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Amasty\HidePrice\Model\Source\Status $status
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Amasty\HidePrice\Model\Source\Status $status,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $context->getStoreManager();
        $this->status = $status;

        parent::__construct($context, $coreRegistry, $customerRepository, $productRepository, $status, $data);
    }

    public function _construct()
    {
        $this->currentRequest = $this->coreRegistry->registry(
            \Amasty\HidePrice\Controller\Adminhtml\Request::CURRENT_REQUEST_MODEL
        );
        parent::_construct();
    }

    public function getRequestData()
    {
        /** @var \Amasty\HidePrice\Model\Request $model */
        $model = $this->getCurrentRequest();
        $customerName = $model->getName();
        try {
            $customer = $this->customerRepository->get($model->getEmail());
        } catch (\Exception $ex) {
            $customer = null;
        }
        if ($customer) {
            $customerName = sprintf(
                '<a href="%s">%s</a>',
                $this->getUrl('customer/index/edit', ['id' => $customer->getId()]),
                $customerName
            );
        }

        $productHtml = $model->getProductId();
        try {
            $product = $this->productRepository->getById($model->getProductId());
        } catch (\Exception $ex) {
            $product = null;
        }
        if ($product) {
            $productHtml =  sprintf(
                '<a href="%s">%s</a>',
                $this->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                $product->getName()
            );
        }

        $store = $this->storeManager->getStore($model->getStore())->getName();
        $status = $this->status->getOptionByValue($model->getStatus());

        $result =  [
            ['label' => __('Customer Name'), 'value' => $customerName],
            ['label' => __('Customer Email'), 'value' => $model->getEmail()],
            ['label' => __('Customer Phone'), 'value' => $model->getPhone()],
            ['label' => __('Customer Company'), 'value' => $model->getCompany()],
            ['label' => __('Product'), 'value' => $productHtml],
            ['label' => __('Quantity'), 'value' => $model->getQuantity()],
            ['label' => __('Store'), 'value' => $store],
            [
                'label' => __('Created'),
                'value' => $this->_localeDate->date($model->getCreatedAt())->format('Y-m-d H:i:s')
            ],
            ['label' => __('Status'), 'value' => $status],
            ['label' => __('Comment'), 'value' => $model->getComment()],
        ];

        $answer = $model->getMessageText();
        if ($answer) {
            $result[] = ['label' => __('Admin Answer Text'), 'value' => $answer];
        }

        return $result;
    }
}
