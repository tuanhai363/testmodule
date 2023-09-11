/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/storage',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, quote, urlManager, errorProcessor, messageContainer, storage, getPaymentInformationAction, totals, $t,
        fullScreenLoader
        ) {
    'use strict';

    return function (isApplied) {
        var quoteId = quote.getQuoteId(),
                url = urlManager.getCancelCouponUrl(quoteId),
                message = $t('Your coupon was successfully removed.');

        messageContainer.clear();
        fullScreenLoader.startLoader();

        return storage.delete(
                url,
                false
                ).done(function () {
            var deferred = $.Deferred();

            totals.isLoading(true);
            getPaymentInformationAction(deferred);
            $.when(deferred).done(function () {
                isApplied(false);
                totals.isLoading(false);
                fullScreenLoader.stopLoader();

                //update points pay form
                $('#ppc_form').find('input[name="ppc_amount"]').val(totals.totals._latestValue.grand_total);
                //regenerate auth checksum
                var url = window.checkoutConfig.payment['flying_blue_payment']['relaodAuthChecksumUrl'];
                var ppc_merchant_transaction_id = window.checkoutConfig.payment['flying_blue_payment']['merchant_transaction_id'];
                var ppc_merchant_code = window.checkoutConfig.payment['flying_blue_payment']['merchant_code'];
                var ppc_merchant_order = window.checkoutConfig.payment['flying_blue_payment']['merchant_order'];
                var ppc_currency_code = window.checkoutConfig.payment['flying_blue_payment']['currency_code'];
                var ppc_amount = totals.totals._latestValue.grand_total;
                var ppc_timestamp = window.checkoutConfig.payment['flying_blue_payment']['timestamp'];

                var param = 'ajax=1&ppc_amount=' + ppc_amount +
                        '&ppc_merchant_transaction_id=' + ppc_merchant_transaction_id +
                        '&ppc_merchant_code=' + ppc_merchant_code +
                        '&ppc_merchant_order=' + ppc_merchant_order +
                        '&ppc_currency_code=' + ppc_currency_code +
                        '&ppc_timestamp=' + ppc_timestamp;
                $.ajax({
                    showLoader: true,
                    url: url,
                    data: param,
                    type: "POST",
                    dataType: 'json'
                }).done(function (data) {
                    //var response = JSON.parse(data)
                    $('#ppc_form').find('input[name="ppc_authorization_checksum"]').val(data.authorization_checksum);
                });
                $('#ppc_checkout_btn').remove();
                var imported = document.createElement('script');
                if (window.checkoutConfig.payment['flying_blue_payment']['test_mode'])
                    imported.src = 'https://uat-secure.pointspay.com/checkout/extjs/ppc-jsloader-min.js';
                else
                {
                    imported.src = 'https://secure.pointspay.com/checkout/extjs/ppc-jsloader-min.js';
                }
                document.head.appendChild(imported);
            });

            messageContainer.addSuccessMessage({
                'message': message
            });
        }).fail(function (response) {
            totals.isLoading(false);
            fullScreenLoader.stopLoader();
            errorProcessor.process(response, messageContainer);
        });
    };
});
