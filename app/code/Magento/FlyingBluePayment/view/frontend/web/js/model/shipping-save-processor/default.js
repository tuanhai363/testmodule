/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'mage/storage',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/select-billing-address',
    //'Magento_Checkout/js/model/shipping-save-processor/payload-extender',
    'jquery',
    'Magento_FlyingBluePayment/js/view/payment/adapter',
], function (
        ko,
        quote,
        resourceUrlManager,
        storage,
        paymentService,
        methodConverter,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction,
        //payloadExtender,
        $,
        ) {
    'use strict';

    return {
        /**
         * @return {jQuery.Deferred}
         */
        saveShippingInformation: function () {
            var payload;

            if (!quote.billingAddress()) {
                selectBillingAddressAction(quote.shippingAddress());
            }

            payload = {
                addressInformation: {
                    'shipping_address': quote.shippingAddress(),
                    'billing_address': quote.billingAddress(),
                    'shipping_method_code': quote.shippingMethod()['method_code'],
                    'shipping_carrier_code': quote.shippingMethod()['carrier_code']
                }
            };
            /*if (payloadExtender && typeof payloadExtender === 'function') { 
                payloadExtender(payload);
            }*/             

            fullScreenLoader.startLoader();

            return storage.post(
                    resourceUrlManager.getUrlForSetShippingInformation(quote),
                    JSON.stringify(payload)
                    ).done(
                    function (response) {

                        //update points pay form
                        if ($('#ppc_form').length)
                        {
                            $('input[name="ppc_amount_temp"]').remove();
                            $('input[name="ppc_authorization_checksum_temp"]').remove();
                            $('#ppc_form').find('input[name="ppc_amount"]').val(response.totals.base_grand_total);
                        } else
                        {
                            var ppc_amount_temp = document.createElement('input');
                            ppc_amount_temp.setAttribute('name', 'ppc_amount_temp');
                            ppc_amount_temp.setAttribute('type', 'hidden');
                            ppc_amount_temp.setAttribute('value', response.totals.base_grand_total);
                            document.head.appendChild(ppc_amount_temp);
                        }

                        //regenerate auth checksum
                        var url = window.checkoutConfig.payment['flying_blue_payment']['relaodAuthChecksumUrl'];
                        var ppc_merchant_transaction_id = window.checkoutConfig.payment['flying_blue_payment']['merchant_transaction_id'];
                        var ppc_merchant_code = window.checkoutConfig.payment['flying_blue_payment']['merchant_code'];
                        var ppc_merchant_order = window.checkoutConfig.payment['flying_blue_payment']['merchant_order'];
                        var ppc_currency_code = window.checkoutConfig.payment['flying_blue_payment']['currency_code'];
                        var ppc_amount = response.totals.base_grand_total;
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
                            if ($('#ppc_form').length)
                            {
                                $('#ppc_form').find('input[name="ppc_authorization_checksum"]').val(data.authorization_checksum);
                            } else
                            {
                                var ppc_authorization_checksum_temp = document.createElement('input');
                                ppc_authorization_checksum_temp.setAttribute('name', 'ppc_authorization_checksum_temp');
                                ppc_authorization_checksum_temp.setAttribute('type', 'hidden');
                                ppc_authorization_checksum_temp.setAttribute('value', data.authorization_checksum);
                                document.head.appendChild(ppc_authorization_checksum_temp);
                            }

                        });

                        var imported = document.createElement('script');
                        if (window.checkoutConfig.payment['flying_blue_payment']['test_mode'])
                            imported.src = 'https://uat-secure.pointspay.com/checkout/extjs/ppc-jsloader-min.js';
                        else
                        {
                            imported.src = 'https://secure.pointspay.com/checkout/extjs/ppc-jsloader-min.js';
                        }


                        quote.setTotals(response.totals);
                        paymentService.setPaymentMethods(methodConverter(response['payment_methods']));
                        jQuery('#ppc_checkout_btn').remove();
                        document.head.appendChild(imported);
                        fullScreenLoader.stopLoader();
                    })
                    .fail(
                            function (response) {
                                errorProcessor.process(response);
                                fullScreenLoader.stopLoader();
                            }
                    )
        }
    };
});
