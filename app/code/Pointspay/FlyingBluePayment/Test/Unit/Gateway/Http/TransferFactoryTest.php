<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Pointspay\FlyingBluePayment\Test\Unit\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Pointspay\FlyingBluePayment\Gateway\Http\TransferFactory;
use Pointspay\FlyingBluePayment\Gateway\Request\MockDataRequest;
use Pointspay\FlyingBluePayment\Gateway\Config\Config;

class TransferFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $request = [
            'parameter' => 'value',
            MockDataRequest::FORCE_RESULT => 1
        ];
        $clientConfig = [
            'timeout' => 60,
        ];

        $configMock = $this->createMock(Config::class);
        $transferBuilderMock = $this->createMock(TransferBuilder::class);
        $transferObjectMock = $this->createMock(TransferInterface::class);

        // Sample request data
        $request = ['sample' => 'data'];

        // Configure expectations on TransferBuilder
        $transferBuilderMock->expects($this->once())
            ->method('setClientConfig')
            ->willReturnSelf();

        $transferBuilderMock->expects($this->once())
            ->method('setHeaders')
            ->willReturnSelf();

        $transferBuilderMock->expects($this->once())
            ->method('setUri')
            ->willReturnSelf();

        $transferBuilderMock->expects($this->once())
            ->method('setBody')
            ->with($request)
            ->willReturnSelf();

        $transferBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($transferObjectMock);

        // Instantiate the TransferFactory with mocks
        $transferFactory = new TransferFactory($configMock, $transferBuilderMock);

        // Call the method being tested
        $result = $transferFactory->create($request);

        // Assertions
        $this->assertSame($transferObjectMock, $result);
    }
}
