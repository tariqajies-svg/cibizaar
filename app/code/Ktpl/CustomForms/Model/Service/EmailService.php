<?php

namespace Ktpl\CustomForms\Model\Service;

use Ktpl\CustomForms\Helper\Data as DataHelper;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Ktpl\CustomForms\Model\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Laminas\Mime\Mime;


/**
 * Send all extension email from here
 */
class EmailService
{
    const EMAIL_REQUEST_QUOTE = 'request_quote';


    /**
     * @var DataHelper
     */
    private $helper;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;
    

    /**
     * @var object
     */
    private $store;

    /**
     * @var array
     */
    private $templateVars;

    /**
     * @var array
     */
    private $attachment;

    /**
     * @var array
     */
    private $mailTo;

    /**
     * @var string
     */
    private $template;

    /**
     * @var array|null
     */
    private $bcc;

    /**
     * @var array|null
     */
    public $senderResolver;

    /**
     * Constructor
     *
     * @param DataHelper $helper
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        DataHelper $helper,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        SenderResolverInterface $senderResolver
    ) {
        $this->helper = $helper;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->senderResolver = $senderResolver;
    }

    public function getTemplates()
    {
        return [
            self::EMAIL_REQUEST_QUOTE => DataHelper::XML_PATH_REQUEST_QUOTE_EMAIL
        ];
    }

    public function getBccRecipent()
    {
        return [
            self::EMAIL_REQUEST_QUOTE => DataHelper::XML_PATH_REQUEST_QUOTE_EMAIL_BCC
        ];
    }

    private function getTemplateData($key = null)
    {
        $data = $this->getTemplates();
        if ($key && !empty($data[$key])) {
            return $data[$key];
        }
        return null;
    }

    private function getBccData($key = null)
    {
        $data = $this->getBccRecipent();
        if ($key && !empty($data[$key])) {
            return $data[$key];
        }
        return null;
    }

    public function send()
    {
        $result = null;
        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($this->getTemplate())
            ->setTemplateVars($this->getTemplateVars())
            ->setTemplateOptions($this->getTemplateOption())
            ->setFrom($this->getSender())
            ->addTo($this->getSendTo());

        if (!empty($this->getBcc())) {
            $transport->addBcc($this->getBcc());
        }

        if (!empty($this->getAttachment())) {
            $transport->addFormAttachment($this->getAttachment());
        }

        try {
            $result = $transport->getTransport()->sendMessage();

            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            return null;
        }
        return $result;
    }

    public function setStoreId($storeId)
    {
        $this->store = $storeId;
    }

    private function getStoreId()
    {
        return $this->store;
    }

    /**
     * Set Email Template Variable
     * array(
     *    'message'   => "This is sample test email from EmailService Class.."
     * );
     * @param array $data
     */
    public function setTemplateVars($data)
    {
        $this->templateVars = $data;
        return $this;
    }

    private function getTemplateVars()
    {
        return $this->templateVars;
    }

    public function setAttachment($data)
    {
        $this->attachment = $data;
        return $this;
    }

    private function getAttachment()
    {
        return $this->attachment;
    }

    private function getTemplate()
    {
        return $this->template;
    }

    private function getTemplateOption()
    {
        $area = \Magento\Framework\App\Area::AREA_FRONTEND;
        $storeId = $this->getStoreId();

        return [
            'area' => $area,
            'store' => $storeId
        ];
    }

    private function getSender()
    {
        $sender = $this->helper->getScopeValue(DataHelper::XML_PATH_CUSTOMFORMS_SENDER, $this->getStoreId());
        return $this->senderResolver->resolve($sender, $this->getStoreId());
    }

    public function setSendTo($data)
    {
        $this->mailTo = $data;
        return $this;
    }

    private function getSendTo()
    {
        return $this->mailTo;
    }

    private function setEmailTemplateBcc($type)
    {
        $template = $this->getTemplateData($type) ?? null;
        $bcc = $this->getBccData($type) ?? null;

        $this->setTemplate($template);
        $this->setBcc($bcc);
    }

    public function setType($type)
    {
        $this->setEmailTemplateBcc($type);
        return $this;
    }

    private function setTemplate($template)
    {
        $this->template = $this->helper->getScopeValue($template, $this->getStoreId());
        return $this;
    }

    private function setBcc($mailpath)
    {
        $bcc = $this->helper->getScopeValue($mailpath, $this->getStoreId());
        if ($bcc) {
            $this->bcc = explode(',', $bcc);
        }
    }

    private function getBcc()
    {
        return $this->bcc;
    }
}
