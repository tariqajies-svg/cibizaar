<?php
/**
 * This file is part of the Magebit_HyvaAheadworksCa package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaAheadworksCa
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaAheadworksCa\Block\Company;

use JsonException;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Rendering js layout for company form
 */
class Form extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Ca::form.phtml';

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @var string
     */
    private string $companyName = '';

    /**
     * @param Context $context
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Escaper $escaper,
        array   $data = []
    ) {
        parent::__construct($context, $data);

        $this->escaper = $escaper;
    }

    /**
     * JS layout for company edit form
     *
     * @throws JsonException
     */
    public function getJsLayout()
    {
        $dataProvider = $this->getFormViewModel()->getDataProvider();
        $this->jsLayout = $dataProvider->modifyMeta($this->jsLayout);
        $id = $this->_request->getParam($dataProvider->getRequestFieldName());
        $data = $dataProvider->getData();
        $firstElementIndex = array_key_first($data);

        if ($firstElementIndex) {
            if (isset($data[$firstElementIndex]['company'])) {
                $data[$firstElementIndex]['company']['name'] = $this->escaper->escapeHtml(
                    $data[$firstElementIndex]['company']['name']
                );
                $this->companyName = $data[$firstElementIndex]['company']['name'];
                $data[$firstElementIndex]['company']['street'] = $this->escaper->escapeHtml(
                    $data[$firstElementIndex]['company']['street']
                );
                $data[$firstElementIndex]['company']['city'] = $this->escaper->escapeHtml(
                    $data[$firstElementIndex]['company']['city']
                );
                $data[$firstElementIndex]['company']['tax_id'] = $this->escaper->escapeHtml(
                    $data[$firstElementIndex]['company']['tax_id']
                );
                $data[$firstElementIndex]['company']['postcode'] = $this->escaper->escapeHtml(
                    $data[$firstElementIndex]['company']['postcode']
                );
            }

            if (isset($data[$firstElementIndex]['extension_attributes'])) {
                $data[$firstElementIndex]['extension_attributes']['aw_ca_company_user']['telephone'] =
                    $this->escaper->escapeHtml(
                        $data[$firstElementIndex]['extension_attributes']['aw_ca_company_user']['telephone']
                    );
            }
        }

        if ($id && isset($data[$id])) {
            $this->jsLayout['components'][$dataProvider->getName()]['data'] = $data[$id];
        }
        return json_encode($this->jsLayout, JSON_THROW_ON_ERROR);
    }

    /**
     * Get Company's name for title
     *
     * @return string
     */
    public function getCompanyName(): string
    {
        if (!$this->companyName) {
            $dataProvider = $this->getFormViewModel()->getDataProvider();
            $data = $dataProvider->getData();
            $firstElementIndex = array_key_first($data);
            if ($firstElementIndex) {
                if (isset($data[$firstElementIndex]['company'])) {
                    $data[$firstElementIndex]['company']['name'] = $this->escaper->escapeHtml(
                        $data[$firstElementIndex]['company']['name']
                    );
                    $this->companyName = $data[$firstElementIndex]['company']['name'];
                }
            }
        }

        return $this->companyName;
    }
}
