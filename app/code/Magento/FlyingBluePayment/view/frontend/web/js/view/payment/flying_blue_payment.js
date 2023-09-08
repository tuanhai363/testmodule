/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
        [
            'ArriveJs',
            'uiComponent',
            'Magento_Checkout/js/model/payment/renderer-list',
            'Magento_Checkout/js/action/get-payment-information',
        ],
        function (
                ArriveJs,
                Component,
                rendererList,
                getPaymentInformationAction,
                ) {
            'use strict';
            rendererList.push(
                    {
                        type: 'flying_blue_payment',
                        component: 'Magento_FlyingBluePayment/js/view/payment/method-renderer/flying_blue_payment'
                    }
            );

            /** Add view logic here if needed */
            return Component.extend({});

        }
);

define(['jquery', 'domReady!'], function ($) {

    $(document).ready(function () {

        //wait until the last element (.payment-method) being rendered
        var existCondition = setInterval(function () {
            if ($('.payment-method').length) {
                
                $('body').on('click','a[id="ppc_checkout_btn"][wl="true"]', function() {
                    initFlyingBlueFormObserver();
                });

                clearInterval(existCondition);
                getFlyingBlueButton();
            }
        }, 100);

        setInterval(checkFocus, 100);
    });
    function getFlyingBlueButton() {
       // $('a[id="ppc_checkout_btn"][wl="true"]').remove();
    }

    function checkFocus() {
        //PPC.app.asyncRequestPPC();
        if (typeof $('a[id="ppc_checkout_btn"][wl="true"]') !== "undefined" && $('a[id="ppc_checkout_btn"][wl="true"]').attr('onclick') !== false)
        {
            //prevent redirect to points pay
            $('a[id="ppc_checkout_btn"][wl="true"]').attr("onclick", "(function(){})()");
        }
    }
    function initFlyingBlueFormObserver() {
                jQuery('body').trigger('processStart');

                var current_amount = $('input[name="ppc_amount"]').val();

                var url = window.checkoutConfig.payment['flying_blue_payment']['getUpdatedOrderDataRequestURL'];

                var param = 'ajax=1';
                $.ajax({
                    showLoader: true,
                    url: url,
                    data: param,
                    type: "POST",
                    dataType: 'json'
                }).done(function (current) {
                    if (current.error)
                    {
                        // alert(current.error);
                        jQuery('body').trigger('processStop');
                        return false;
                    } else
                    {
                        var url = window.checkoutConfig.payment['flying_blue_payment']['getPlaceOrderRequestURL'];

                        var ppc_merchant_order = window.checkoutConfig.payment['flying_blue_payment']['merchant_order'];
                        var param = 'ajax=1&ppc_order_id=' + ppc_merchant_order;


                        $.ajax({
                            showLoader: true,
                            url: url,
                            data: param,
                            type: "POST",
                            dataType: 'json'
                        }).done(function (data) {

                            if (data.error)
                            {
                                // alert(data.error);
                                jQuery('body').trigger('processStop');
                            } else
                            {
                                if (parseFloat(current.ppc_amount) != parseFloat(current_amount))
                                {

                                    var urltemp = data;

                                    //update points pay form
                                    $('#ppc_form').find('input[name="ppc_amount"]').val(current.ppc_amount);
                                    //regenerate auth checksum
                                    var url = window.checkoutConfig.payment['flying_blue_payment']['relaodAuthChecksumUrl'];
                                    var ppc_merchant_transaction_id = window.checkoutConfig.payment['flying_blue_payment']['merchant_transaction_id'];
                                    var ppc_merchant_code = window.checkoutConfig.payment['flying_blue_payment']['merchant_code'];
                                    var ppc_merchant_order = window.checkoutConfig.payment['flying_blue_payment']['merchant_order'];
                                    var ppc_currency_code = window.checkoutConfig.payment['flying_blue_payment']['currency_code'];
                                    var ppc_amount = current.ppc_amount;
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

                                        if(urltemp.url)
                                        {
                                            
                                            require([
                                                'Magento_Customer/js/customer-data'
                                            ], function (customerData) {
                                                var sections = ['cart'];
                                                customerData.invalidate(sections);
                                               customerData.reload(sections, true);
                                            });
                                            
                                            window.location.href = urltemp.url;
                                            return false;
                                        }
                                        else
                                        {
                                            console.dir(urltemp.msg);
                                        }

                                        
                                    });
                                }
                                else
                                {

                                    if(data.url)
                                    {
                                        
                                        require([
                                            'Magento_Customer/js/customer-data'
                                        ], function (customerData) {
                                            var sections = ['cart'];
                                            customerData.invalidate(sections);
                                           customerData.reload(sections, true);
                                        });
                                        
                                        window.location.href = data.url;
                                        return false;
                                    }
                                    else
                                    {
                                        console.dir(data.msg);
                                    }

                                   
                                }
                            }
                        });
                    }
                });


    }
}(jQuery)
        );
