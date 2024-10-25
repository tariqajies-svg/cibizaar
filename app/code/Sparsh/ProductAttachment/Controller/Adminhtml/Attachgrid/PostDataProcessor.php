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
namespace Sparsh\ProductAttachment\Controller\Adminhtml\Attachgrid;

/**
 * Class PostDataProcessor
 *
 * @category Sparsh
 * @package  Sparsh_ProductAttachment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class PostDataProcessor
{
    /**
     * Date Filter
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * Validation Factory
     *
     * @var \Magento\Framework\View\Model\Layout\Update\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * Message Manager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * PostDataProcessor constructor.
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory
    ) {
        $this->dateFilter = $dateFilter;
        $this->messageManager = $messageManager;
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * Validate post data
     *
     * @param array $data Datapost
     *
     * @return bool
     */
    public function validate($data)
    {
        $errorNo = true;
        $errorNo = $this->validateRequireEntry($data);

        if ($errorNo) {
            if (!in_array($data['is_active'], [0,1]) || $data['is_active'] == '' || $data['is_active'] === null) {
                $errorNo = false;
                $this->messageManager->addError(
                    __("Please enter valid value to status field.")
                );
            }
        }

        return $errorNo;
    }

    /**
     * Check if required fields is not empty
     *
     * @param array $data RequireFields
     *
     * @return bool
     */
    public function validateRequireEntry(array $data)
    {
        $requiredFields = [
            'title' => __('Title'),
            'is_active' => __('Status')
        ];
        $errorNo = true;
        foreach ($data as $field => $value) {
            if (in_array($field, array_keys($requiredFields)) && $value == '') {
                $errorNo = false;
                $this->messageManager->addErrorMessage(
                    __('To apply changes you should fill valid value to required "%1" field', $requiredFields[$field])
                );
            }
        }
        return $errorNo;
    }
}
