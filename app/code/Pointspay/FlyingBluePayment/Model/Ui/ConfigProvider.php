<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pointspay\FlyingBluePayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Pointspay\FlyingBluePayment\Gateway\Config\Config;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'flying_blue_payment';

    /**
     * @var Config
     */
    protected $config;

    /**
     * ConfigProvider constructor.
     * @param Config $config
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'relaodAuthChecksumUrl' => $this->config->getReloadAuthChecksumUrl(),
                    'getUpdatedOrderDataRequestURL' => $this->config->getUpdatedOrderDataRequestURL(),
                    'getPlaceOrderRequestURL' => $this->config->getPlaceOrderRequestURL(),
                    'test_mode' => $this->config->isTestMode(),
                    'merchant_code' => $this->config->getMerchantCode(),
                    'merchant_transaction_id' => $this->config->getTID(),
                    'merchant_order' => $this->config->getOrderID(),
                    'currency_code' => $this->config->getCurrencyCode(),
                    'amount' => $this->config->getOrderAmount() ? number_format($this->config->getOrderAmount(), 2, '.', '') : 0,
                    'timestamp' => (int)$this->config->getTimeStamp(),
                    'url_success' => (is_array($this->config->getRedirectURLs()) && isset($this->config->getRedirectURLs()['success'])) ? $this->config->getRedirectURLs()['success'] : '',
                    'url_failure' => (is_array($this->config->getRedirectURLs()) && isset($this->config->getRedirectURLs()['failure'])) ? $this->config->getRedirectURLs()['failure'] : '',
                    'url_cancel' => (is_array($this->config->getRedirectURLs()) && isset($this->config->getRedirectURLs()['cancel'])) ? $this->config->getRedirectURLs()['cancel'] : '',
                    'authorization_checksum' => $this->config->getAuthChecksum(array(
                        'ppc_merchant_transaction_id' => $this->config->getTID(),
                        'ppc_merchant_code' => $this->config->getMerchantCode(),
                        'ppc_merchant_order' => $this->config->getOrderID(),
                        'ppc_currency_code' => $this->config->getCurrencyCode(),
                        'ppc_amount' => $this->config->getOrderAmount() ? number_format($this->config->getOrderAmount(), 2, '.', '') : 0,
                        'ppc_language' => 'en',
                        'ppc_timestamp' => (int)$this->config->getTimeStamp()
                    )),
                    'url_img' => 'https://' . ($this->config->isTestMode() ? 'uat-flb.pointspay.com' : 'plus-secure.flyingblue.com') . '/checkout/user/btn-img-v2?s=' . $this->config->getMerchantCode(),
                    'paymentAcceptanceMarkSrc' => 'https://' . ($this->config->isTestMode() ? 'uat-flb.pointspay.com' : 'plus-secure.flyingblue.com') . '/checkout/user/btn-img-v2?s=' . $this->config->getMerchantCode(),
                    'payment_disabled_descrption' => $this->config->getPaymentDisabledDesc()
                ]
            ]
        ];
    }
}
