/**
 * This file is part of the Magebit/bizaar_fallback design package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit/bizaar
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Magento_Checkout/js/view/shipping-extend': true
            },
            'Magento_Ui/js/lib/validation/validator': {
                'Smile_ElasticsuiteCore/js/validation/validator-mixin': false
            }
        }
    }
}
