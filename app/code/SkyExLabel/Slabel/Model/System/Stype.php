<?php

namespace SkyExLabel\Slabel\Model\System;

use Magento\Framework\Option\ArrayInterface;

class Stype implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {

        return  [
                    [
                        'value' => 1, 
                        'label' => __('Document')
                    ], 
                    [
                        'value' => 2, 
                        'label' => __('Non Document')
                    ],
                ];
    }

}