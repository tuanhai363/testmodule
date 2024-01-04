define([
    'jquery',
], function ($) {
    'use strict';

    return {

        /**
         * Get payment name.
         * @returns {String}
         */
        getCode: function () {
            return 'flying_blue_payment';
        },
        isTestMode: function () {
            return this.getPaymentConfig()['test_mode'];
        },
        /**
         * Get payment configuration array.
         * @returns {Array}
         */
        getPaymentConfig: function () {
            return window.checkoutConfig.payment[this.getCode()];
        },
        /**
         *
         * @param currencyCode
         * @param amount
         * @returns {number}
         */
        getAmountForGateway: function (currencyCode, amount) {
            var priceAdapter = this.getPaymentConfig()['priceAdapter'];

            amount = parseFloat(amount);

            if (priceAdapter[currencyCode] === undefined) {
                return amount * priceAdapter['others'];
            }

            return amount * priceAdapter[currencyCode];
        }
    };
});
