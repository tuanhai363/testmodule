/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Pointspay_FlyingBluePayment/js/view/payment/adapter',
        'Magento_Customer/js/model/customer',
    ],
    function (Component, FlyingBluePayment, customer) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Pointspay_FlyingBluePayment/payment/form',
            },
            isCustomerLoggedIn: customer.isLoggedIn, /*return  boolean true/false */
            initialize: function () {
                this._super();
                /* check using below method */
                var isLoggedIn = this.isCustomerLoggedIn();
            },
            /**
             * @returns {string}
             */
            getMerchantCode: function () {
                return FlyingBluePayment.getPaymentConfig()['merchant_code'];
            },
            /**
             * @returns {string}
             */
            getOrderID: function () {
                return FlyingBluePayment.getPaymentConfig()['merchant_order'];
            },

            isCustomerLogged: function () {
                if (!customer.isLoggedIn()) {
                    return "disabled";
                }
            },

            getPaymentDisabledDescrption: function () {
                return FlyingBluePayment.getPaymentConfig()['payment_disabled_descrption'];
            },
            isCustomerLoggedDisplayInfo: function () {
                if (!customer.isLoggedIn()) {
                    return 'margin-top: 10px !important;';
                }

                return 'display: none;';
            },

            /**
             * @returns {string}
             */
            getMerchantTID: function () {
                return FlyingBluePayment.getPaymentConfig()['merchant_transaction_id'];
            },

            /**
             * @returns {string}
             */
            getAuthChecksum: function () {
                return FlyingBluePayment.getPaymentConfig()['authorization_checksum'];
            },

            /**
             * @returns {string}
             */
            getCurrencyCode: function () {
                return FlyingBluePayment.getPaymentConfig()['currency_code'];
            },

            /**
             * @returns {string}
             */
            getRedirectURL: function (action) {

                return FlyingBluePayment.getPaymentConfig()[action];
            },

            getUrlImg: function () {
                return FlyingBluePayment.getPaymentConfig()['url_img'];
            },

            /**
             * @returns {string}
             */
            getAmount: function () {
                return FlyingBluePayment.getPaymentConfig()['amount'];
            },
            /**
             * @returns {string}
             */
            getTimeStamp: function () {
                return FlyingBluePayment.getPaymentConfig()['timestamp'];
            },
            /** Returns payment acceptance mark image path */
            getPaymentAcceptanceMarkSrc: function () {
                return FlyingBluePayment.getPaymentConfig()['paymentAcceptanceMarkSrc'];
            },
        });
    }
);
