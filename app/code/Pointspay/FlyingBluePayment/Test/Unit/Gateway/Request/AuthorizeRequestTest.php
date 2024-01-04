<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Pointspay\FlyingBluePayment\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Pointspay\FlyingBluePayment\Gateway\Request\AuthorizationRequest;

class AuthorizeRequestTest extends \PHPUnit\Framework\TestCase
{
    public function testBuild()
    {
        $merchantToken = 'secure_token';
        $invoiceId = 1001;
        $grandTotal = 12.2;
        $currencyCode = 'USD';
        $storeId = 1;
        $email = 'user@domain.com';

        $expectation = [
            'TXN_TYPE' => 'A',
            'INVOICE' => $invoiceId,
            'AMOUNT' => $grandTotal,
            'CURRENCY' => $currencyCode
        ];

        $configMock = $this->createMock(ConfigInterface::class);
        $orderMock = $this->createMock(OrderAdapterInterface::class);
        $addressMock = $this->createMock(AddressAdapterInterface::class);
        $payment = $this->createMock(PaymentDataObjectInterface::class);

        $payment->expects(static::any())
            ->method('getOrder')
            ->willReturn($orderMock);

        $orderMock->expects(static::any())
            ->method('getShippingAddress')
            ->willReturn($addressMock);

        $orderMock->expects(static::once())
            ->method('getOrderIncrementId')
            ->willReturn($invoiceId);
        $orderMock->expects(static::once())
            ->method('getGrandTotalAmount')
            ->willReturn($grandTotal);
        $orderMock->expects(static::once())
            ->method('getCurrencyCode')
            ->willReturn($currencyCode);
        $orderMock->expects(static::any())
            ->method('getStoreId')
            ->willReturn($storeId);

        $request = new AuthorizationRequest();

        static::assertEquals(
            $expectation,
            $request->build(['payment' => $payment])
        );
    }
}
