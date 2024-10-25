<?php
/**
 * This file is part of the Magebit_Bizaar package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Bizaar\Rewrite\Amasty\Shiprestriction\Model;

use Amasty\Shiprestriction\Model\CanShowMessageOnce;
use Amasty\Shiprestriction\Model\Message\RestrictionMessageProcessor;
use Amasty\Shiprestriction\Model\RestrictRatesPerCarrier as RestrictRatesPerCarrierOriginal;
use Amasty\Shiprestriction\Model\Rule;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Rate\CarrierResult;

/**
 * Overriding original class to adjust method - appendError
 * We need to include all data into error.
 */
class RestrictRatesPerCarrier extends RestrictRatesPerCarrierOriginal
{
    /**
     * Ignore phpcs for constructor to avoid "Possible useless method overriding detected" error
     *
     * @param ErrorFactory $rateErrorFactory
     * @param CanShowMessageOnce $canShowMessageOnce
     * @param RestrictionMessageProcessor $messageProcessor
     */
    // phpcs:disable
    public function __construct(
        private readonly ErrorFactory $rateErrorFactory,
        private readonly CanShowMessageOnce $canShowMessageOnce,
        private readonly RestrictionMessageProcessor $messageProcessor
    ) {
        parent::__construct($rateErrorFactory, $canShowMessageOnce, $messageProcessor);
    }
    // phpcs:enable

    /**
     * @param CarrierResult $result
     * @param string $carrierCode
     * @param Method[] $carrierRates
     * @param Rule[] $rules
     * @return void
     */
    public function execute(
        CarrierResult $result,
        string $carrierCode,
        array $carrierRates,
        array $rules
    ): void {
        foreach ($carrierRates as $rate) {
            $restrict = false;

            foreach ($rules as $rule) {
                if ($rule->match($rate)) {
                    $restrict = true;

                    $message = false;
                    // collect amstrates methods to one string in case "Show Restriction Message Once" = Yes
                    if ($rate->getCarrier() === 'amstrates'
                        && $this->canShowMessageOnce->execute($rule, $carrierCode)
                    ) {
                        $methodTitles[] = $rate->getMethodTitle();
                        // all rate method_title(s) are collected and ready to be added to the final message
                        // after the last rate is processed.
                        if ($rate === $carrierRates[array_key_last($carrierRates)]) {
                            $rate->setData('method_title', implode(', ', $methodTitles));
                            $message = $this->messageProcessor->process($rate, $rule);
                        }
                    } else {
                        $message = $this->messageProcessor->process($rate, $rule);
                    }

                    if ($message) {
                        if ($rate instanceof Error) {
                            $rate->setErrorMessage($message);
                            $result->append($rate);
                        } else {
                            $this->appendError($result, $rate, $message);
                        }

                        if ($this->canShowMessageOnce->execute($rule, $carrierCode)) {
                            return;
                        }

                        break;
                    }
                }
            }

            if (!$restrict) {
                $result->append($rate);
            }
        }
    }

    /**
     * Overriding original class to adjust method - appendError
     * We need to include all data into error.
     *
     * @param CarrierResult $result
     * @param Method $rate
     * @param string $errorMessage
     * @return void
     */
    private function appendError(CarrierResult $result, Method $rate, string $errorMessage): void
    {
        /** @var Error $error */
        $error = $this->rateErrorFactory->create();
        foreach ($rate->getData() as $key => $data) {
            $error->setData($key, $data);

        }
        $error->setData('error_message', $errorMessage);

        $result->append($error);
    }
}
