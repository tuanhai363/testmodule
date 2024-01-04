<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Pointspay\FlyingBluePayment\Test\Unit\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Pointspay\FlyingBluePayment\Gateway\Response\TxnIdHandler;

class TxnIdHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testHandle()
    {
        $response = [
            TxnIdHandler::TXN_ID => ['fcd7f001e9274fdefb14bff91c799306'],
            'status' => TxnIdHandler::STATUS
        ];

        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        /*$paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($paymentModel);

        $paymentModel->expects(static::once())
            ->method('setTransactionId')
            ->with($response[TxnIdHandler::TXN_ID]);

        $paymentModel->expects(static::once())
            ->method('setIsTransactionClosed')
            ->with(false);*/

        $helper = $this->createMock(\Pointspay\FlyingBluePayment\Helper\Data::class);
        $request = new TxnIdHandler($helper);
        $request->handle(['payment' => $paymentDO], $response);
    }
}
