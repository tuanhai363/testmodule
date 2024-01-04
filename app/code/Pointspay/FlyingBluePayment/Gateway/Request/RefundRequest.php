<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pointspay\FlyingBluePayment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Pointspay\FlyingBluePayment\Gateway\Curl;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Pointspay\FlyingBluePayment\Gateway\Curl\PointspayCurl;

class RefundRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var PointspayCurl
     */
    private $curl;

    /**
     * @param ConfigInterface $config
     * @param PointspayCurl $curl
     */
    public function __construct(
        ConfigInterface $config,
        Curl\PointspayCurl $curl
    ) {
        $this->curl = $curl;
        $this->config = $config;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];
        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();
        $response = $this->curl->refundTransaction($order, $payment);
        if (!$response) {
            throw new \InvalidArgumentException('Payment refund has been aborted.');
        }
        if ($response['status'] != 'success') {
            throw new \InvalidArgumentException($response['status_message']);
        }
        if (!$payment instanceof OrderPaymentInterface) {
            throw new \LogicException('Order payment should be provided.');
        }
        $payment->setTransactionId($response['id'] . '-refund');
        return [
            'TXN_TYPE' => 'V',
            'TXN_ID' => $payment->getTransactionId(),
        ];
    }
}
