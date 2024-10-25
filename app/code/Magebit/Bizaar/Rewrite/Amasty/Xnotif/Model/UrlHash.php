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

namespace Magebit\Bizaar\Rewrite\Amasty\Xnotif\Model;

use Amasty\Xnotif\Model\UrlHash as UrlHashParent;
use Magento\Framework\App\Request\Http;

class UrlHash extends UrlHashParent
{
    /**
     * @param Http $request
     * @return bool
     */
    public function check(Http $request)
    {
        $hash = urldecode($request->getParam('hash', ''));
        $productId = $request->getParam('product_id');
        $email = urldecode($request->getParam('email', ''));

        if (empty($hash) || empty($productId) || empty($email)) {
            return false;
        }

        $real = $this->getHash($productId, $email);

        return $hash == $real;
    }
}
