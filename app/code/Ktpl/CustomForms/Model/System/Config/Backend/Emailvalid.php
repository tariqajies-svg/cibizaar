<?php

namespace Ktpl\CustomForms\Model\System\Config\Backend;

class Emailvalid extends \Magento\Framework\App\Config\Value
{

    /**
     * validate comma separated email address.
     *
     * @return $this
     */
    public function beforeSave()
    {
        if (!empty($this->getValue())) {
            $emails = explode(',', $this->getValue());
            $validatorEmail = new \Zend\Validator\EmailAddress();
            $emailValid = false;
            foreach ($emails as $val) {
                if (!$validatorEmail->isValid(trim($val))) {
                    $invalidEmail[] = $val;
                    $emailValid = true;
                }
            }
            if ($emailValid) {
                $invalidEmail = implode(',', $invalidEmail);
                $errorMessages[][] = __("Please enter valid email address $invalidEmail");
                $exception = new \Magento\Framework\Validator\Exception(null, null, $errorMessages);
                throw $exception;
            }
        }

        return $this;
    }
}
