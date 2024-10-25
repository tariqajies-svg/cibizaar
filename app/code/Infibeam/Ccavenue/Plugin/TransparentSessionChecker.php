<?php
namespace Infibeam\Ccavenue\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Session\SessionStartChecker;


class TransparentSessionChecker
{
    const TRANSPARENT_REDIRECT_PATH = 'ccavenue/transparent/redirect';

    /**
     * @var Http
     */
    private $request;

    /**
     * @param Http $request
     */
    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Prevents session starting while instantiating Adyen transparent redirect controller.
     *
     * @param SessionStartChecker $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCheck(SessionStartChecker $subject, bool $result): bool
    {
        if ($result === false) {
            return false;
        }

        return strpos((string)$this->request->getPathInfo(), self::TRANSPARENT_REDIRECT_PATH) === false;
    }
}