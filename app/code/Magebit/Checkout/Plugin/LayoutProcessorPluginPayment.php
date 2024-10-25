<?php
/**
 * This file is part of the Magebit_Checkout package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 */
namespace Magebit\Checkout\Plugin;

use Magento\Checkout\Block\Checkout\LayoutProcessor;

class LayoutProcessorPluginPayment
{
    /**
     * @param LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        LayoutProcessor $subject,
        array $jsLayout
    ): array {
        $paymentForms = $jsLayout['components']['checkout']['children']['steps']['children']
        ['billing-step']['children']['payment']['children']
        ['payments-list']['children'];

        // Change billing field labels for every payment method
        foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {

            $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);

            //Checks if billing step available.
            if (!isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form'])) {
                continue;
            }

            $jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children']
            ['telephone']['component'] = 'Magento_Checkout/js/view/form/element/telephone';

            $jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children']
            ['telephone']['config']['elementTmpl'] = 'Magento_Checkout/form/element/telephone';
        }

        return $jsLayout;
    }
}
