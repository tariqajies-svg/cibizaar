/**
 * This file is part of the Magebit_Analytics package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Dunkin
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

/* jscs:disable */
/* eslint-disable */
define([
    'jquery',
    'mage/cookies'
], function ($) {
    'use strict';

    /**
     * @param {Object} config
     */
    return function (config) {
        var allowServices = false,
            allowedCookies,
            allowedWebsites,
            measurementId;

        if (config.isCookieRestrictionModeEnabled) {
            allowedCookies = $.mage.cookies.get(config.cookieName);

            if (allowedCookies !== null) {
                allowedWebsites = JSON.parse(allowedCookies);

                if (allowedWebsites[config.currentWebsite] === 1) {
                    allowServices = true;
                }
            }
        } else {
            allowServices = true;
        }

        if (allowServices) {
            /* Global site tag (gtag.js) - Google Analytics */
            measurementId = config.pageTrackingData.measurementId;
            if (window.gtag) {
                gtag('config', measurementId, {'anonymize_ip': true});
                // Purchase Event
                if (config.ordersTrackingData.hasOwnProperty('currency')) {
                    var purchaseObject = config.ordersTrackingData.orders[0];
                    purchaseObject['items'] = config.ordersTrackingData.products;
                    gtag('event', 'purchase', purchaseObject);
                }
            } else {
                (function (d, s, u) {
                    var gtagScript = d.createElement(s);
                    gtagScript.type = 'text/javascript';
                    gtagScript.async = true;
                    gtagScript.src = u;
                    d.head.insertBefore(gtagScript, d.head.children[0]);
                })(document, 'script', 'https://www.googletagmanager.com/gtag/js?id=' + measurementId);
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }

                window.gtag = gtag;

                gtag('js', new Date());
                gtag('set', 'developer_id.dYjhlMD', true);
                gtag('config', measurementId, {'anonymize_ip': true});

                // Purchase Event
                if (config.ordersTrackingData.hasOwnProperty('currency')) {
                    var purchaseObject = config.ordersTrackingData.orders[0];
                    purchaseObject['items'] = config.ordersTrackingData.products;
                    gtag('event', 'purchase', purchaseObject);
                }

                // Page-view Event
                gtag('event', 'page_view', {
                    page_title: document.title,
                    page_location: window.location.href,
                    send_page_view: true
                });

                let isPDPView = document.querySelector('body').classList.contains('catalog-product-view');

                if (isPDPView) {
                    // Page-view Event
                    gtag('event', 'view_item', {
                        currency: config.current_currency,
                        value: parseFloat(document.querySelector('meta[itemprop="price"]').content),
                        items: [
                            {
                                item_id: document.querySelector('div[itemprop="sku"]').innerHTML,
                                item_name: document.querySelector('.page-title').innerText,
                            }
                        ]
                    });


                    $('button.tocart').click(function(){
                        gtag('event', 'add_to_cart', {
                            currency: config.current_currency,
                            value: parseFloat(document.querySelector('meta[itemprop="price"]').content),
                            items: [
                                {
                                    item_id: document.querySelector('div[itemprop="sku"]').innerHTML,
                                    item_name: document.querySelector('.page-title').innerText,
                                }
                            ]
                        });
                    });
                }
            }
        }
    }
});
