/**
 * Magedelight
 * Copyright (C) 2023 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */
define(['jquery'], function ($) {
    return function (config, element) {
        $('.magedelight-promo-tracking').show('slow', function () {

            var promoId = $(this).attr('data-promo-id');
            var promoName = $(this).attr('data-promo-name');
            var promoCreativeName = $(this).attr('data-creative-name');
            var promoCreativeSlot = $(this).attr('data-creative-slot');
            if (config.view_promotion) {
                $.ajax({
                    url: config.customurl,
                    type: 'POST',
                    data: {
                        pid: $(this).attr('data-product-id'),
                        cid: $(this).attr('data-category-id'),
                        promoId:promoId,
                        promoName:promoName,
                        promoCreativeName:promoCreativeName,
                        promoCreativeSlot:promoCreativeSlot,
                        event:'view_promotion'

                    },
                    success: function (response) {
                        if (response.status) {
                            window.dataLayer.push({ecommerce: null});
                            window.dataLayer.push(response.eventData);
                        }
                    },
                    error: function (xhr, status, errorThrown) {
                        console.log('Error happens. Try again.');
                    }
                });
            }
        });

        $('.magedelight-promo-tracking').click(function () {

            var promoId = $(this).attr('data-promo-id');
            var promoName = $(this).attr('data-promo-name');
            var promoCreativeName = $(this).attr('data-creative-name');
            var promoCreativeSlot = $(this).attr('data-creative-slot');
            
            if (config.select_promotion) {
                $.ajax({
                    url: config.customurl,
                    type: 'POST',
                    data: {
                        pid: $(this).attr('data-product-id'),
                        cid: $(this).attr('data-category-id'),
                        promoId:promoId,
                        promoName:promoName,
                        promoCreativeName:promoCreativeName,
                        promoCreativeSlot:promoCreativeSlot,
                        event:'select_promotion'

                    },
                    success: function (response) {
                        if (response.status) {
                            window.dataLayer.push({ecommerce: null});
                            window.dataLayer.push(response.eventData);
                        }
                    },
                    error: function (xhr, status, errorThrown) {
                        console.log('Error happens. Try again.');
                    }
                });
            }
        });
    }
    
});
