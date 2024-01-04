<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Pointspay\FlyingBluePayment\Test\Unit\Gateway\Validator;

use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Pointspay\FlyingBluePayment\Gateway\Http\Client\ClientMock;
use Pointspay\FlyingBluePayment\Gateway\Validator\ResponseCodeValidator;

class ResponseCodeValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResultInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultFactory;

    /**
     * @var ResultInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultMock;

    public function setUp(): void
    {
        $this->resultMock = $this->createMock(ResultInterface::class);

        $this->resultFactory = $this->createMock(ResultInterfaceFactory::class);
        $this->resultFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->resultMock);
    }

    /**
     * @param array $response
     * @param array $expectationToResultCreation
     *
     * @dataProvider validateDataProvider
     */
    public function testValidate(array $response, array $expectationToResultCreation)
    {
        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with(
                $expectationToResultCreation
            )
            ->willReturn($this->resultMock);

        $validator = new ResponseCodeValidator($this->resultFactory);

        static::assertInstanceOf(
            ResultInterface::class,
            $validator->validate(['response' => $response])
        );
    }

    public function validateDataProvider()
    {
        return [
            'fail_1' => [
                'response' => [],
                'expectationToResultCreation' => [
                    'isValid' => false,
                    'failsDescription' => [__('Gateway rejected the transaction.')],
                    'errorCodes' => []
                ]
            ],
            'fail_2' => [
                'response' => [ResponseCodeValidator::RESULT_CODE => ClientMock::FAILURE],
                'expectationToResultCreation' => [
                    'isValid' => false,
                    'failsDescription' => [__('Gateway rejected the transaction.')],
                    'errorCodes' => []
                ]
            ],
            'success' => [
                'response' => [ResponseCodeValidator::RESULT_CODE => ClientMock::SUCCESS],
                'expectationToResultCreation' => [
                    'isValid' => true,
                    'failsDescription' => [],
                    'errorCodes' => []
                ]
            ]
        ];
    }
}
