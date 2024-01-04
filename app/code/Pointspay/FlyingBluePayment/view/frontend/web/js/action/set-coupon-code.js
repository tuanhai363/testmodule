/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'Pointspay_FlyingBluePayment/js/view/payment/adapter'
], function (ko, $, quote, urlManager, errorProcessor, messageContainer, storage, $t, getPaymentInformationAction,
             totals, fullScreenLoader, FlyingBluePayment
) {
    'use strict';

    return function (couponCode, isApplied) {
        var quoteId = quote.getQuoteId(),
            url = urlManager.getApplyCouponUrl(couponCode, quoteId),
            message = $t('Your coupon was successfully applied.');

        fullScreenLoader.startLoader();

        return storage.put(
            url,
            {},
            false
        ).done(function (response) {
            var deferred;

            if (response) {
                deferred = $.Deferred();

                isApplied(true);
                totals.isLoading(true);
                getPaymentInformationAction(deferred);
                $.when(deferred).done(function () {
                    fullScreenLoader.stopLoader();
                    totals.isLoading(false);

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
                    $('#fbc_checkout_btn').remove();
                    var imported = document.createElement('script');
                    if (window.checkoutConfig.payment['flying_blue_payment']['test_mode'])
                        imported.src = 'https://uat-flb.pointspay.com/checkout/extjs/ppc-jsloader-min.js';
                    else {
                        imported.src = 'https://plus-secure.flyingblue.com/checkout/extjs/ppc-jsloader-min.js';
                    }
                    document.head.appendChild(imported);
                });
                messageContainer.addSuccessMessage({
                    'message': message
                });

            }
        }).fail(function (response) {
            fullScreenLoader.stopLoader();
            totals.isLoading(false);
            errorProcessor.process(response, messageContainer);
        });
    };
});
