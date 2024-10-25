<?php
namespace Ktpl\PageBuilderSlider\Plugin\PageBuilder\Model\Wysiwyg\Plugin;

use Magento\PageBuilder\Model\Wysiwyg\Plugin\Slider as OriginalSlider;
use Magento\Framework\Data\OptionSourceInterface;

class SliderOptions
{
    /**
     * @var OptionSourceInterface
     */
    private $customOptions;

    /**
     * @param OptionSourceInterface $customOptions
     */
    public function __construct(OptionSourceInterface $customOptions)
    {
        $this->customOptions = $customOptions;
    }

    /**
     * Add custom options to the slider element
     *
     * @param OriginalSlider $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(OriginalSlider $subject, array $result)
    {

        $result['customOptions'] = $this->customOptions->toOptionArray();
        return $result;
    }
}
