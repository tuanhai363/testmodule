<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pointspay\FlyingBluePayment\Test\Unit\Model\Ui;

use Pointspay\FlyingBluePayment\Gateway\Http\Client\ClientMock;
use Pointspay\FlyingBluePayment\Model\Ui\ConfigProvider;

class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConfig()
    {
        $config = $this->createMock(\Pointspay\FlyingBluePayment\Gateway\Config\Config::class);
        $configProvider = new ConfigProvider($config);
        static::assertEquals(
            [
                'payment' => [
                    ConfigProvider::CODE => [
                        'relaodAuthChecksumUrl' => null,
                        'getUpdatedOrderDataRequestURL' => null,
                        'getPlaceOrderRequestURL' => null,
                        'test_mode' => null,
                        'merchant_code' => null,
                        'merchant_transaction_id' => null,
                        'merchant_order' => null,
                        'currency_code' => null,
                        'amount' => 0,
                        'timestamp' => 0,
                        'url_success' => '',
                        'url_failure' => '',
                        'url_cancel' => '',
                        'authorization_checksum' => null,
                        'url_img' => 'https://plus-secure.flyingblue.com/checkout/user/btn-img-v2?s=',
                        'paymentAcceptanceMarkSrc' => 'https://plus-secure.flyingblue.com/checkout/user/btn-img-v2?s=',
                        'payment_disabled_descrption' => null
                    ]
                ]
            ],
            $configProvider->getConfig()
        );
    }
}
