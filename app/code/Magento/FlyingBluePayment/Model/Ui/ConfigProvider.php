<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\FlyingBluePayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\FlyingBluePayment\Gateway\Config\Config;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'flying_blue_payment';
    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * ConfigProvider constructor.
     * @param Config $config
     */
    public function __construct(Config $config, ResolverInterface $localeResolver)
    {
        $this->config         = $config;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $locale = $this->localeResolver->getLocale();
        return [
            'payment' => [
                self::CODE => [
                    'relaodAuthChecksumUrl'         => $this->config->getReloadAuthChecksumUrl(),
                    'getUpdatedOrderDataRequestURL' => $this->config->getUpdatedOrderDataRequestURL(),
                    'getPlaceOrderRequestURL' => $this->config->getPlaceOrderRequestURL(),
                    'test_mode'                     => $this->config->isTestMode(),
                    'merchant_code'                 => $this->config->getMerchantCode(),
                    'merchant_transaction_id'       => $this->config->getTID(),
                    'merchant_order'                => $this->config->getOrderID(),
                    'currency_code'                 => $this->config->getCurrencyCode(),
                    'amount'                        => number_format($this->config->getOrderAmount(), 2, '.', ''),
                    'timestamp'                     => (int) $this->config->getTimeStamp(),
                    'url_success'                   => $this->config->getRedirectURLs()['success'],
                    'url_failure'                   => $this->config->getRedirectURLs()['failure'],
                    'url_cancel'                    => $this->config->getRedirectURLs()['cancel'],
                    'authorization_checksum'        => $this->config->getAuthChecksum(array(
                        'ppc_merchant_transaction_id' => $this->config->getTID(),
                        'ppc_merchant_code'           => $this->config->getMerchantCode(),
                        'ppc_merchant_order'          => $this->config->getOrderID(),
                        'ppc_currency_code'           => $this->config->getCurrencyCode(),
                        'ppc_amount'                  => number_format($this->config->getOrderAmount(), 2, '.', ''),
                        'ppc_language'                => 'en',
                        'ppc_timestamp'               => (int) $this->config->getTimeStamp()
                    )),
                    'url_img'                       => 'https://secure.pointspay.com/checkout/user/btn-img-v2?s='.$this->config->getMerchantCode(),
                    'paymentAcceptanceMarkSrc'      => 'https://secure.pointspay.com/checkout/user/btn-img-v2?s='.$this->config->getMerchantCode(),
                    'payment_disabled_descrption'   => $this->config->getPaymentDisabledDesc()
                ]
            ]
        ];
    }
}
