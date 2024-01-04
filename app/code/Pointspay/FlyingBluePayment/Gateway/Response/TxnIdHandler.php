<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pointspay\FlyingBluePayment\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Pointspay\FlyingBluePayment\Helper\Data;

class TxnIdHandler implements HandlerInterface
{
    const TXN_ID = 'TXN_ID';
    const STATUS = 'SUCCESS';
    /**
     * @var Pointspay\FlyingBluePayment\Helper\Data
     */
    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $requestURI = $_POST;
        if (isset($requestURI['status'])) {
            $params = array(
                'status' => $requestURI['status'],
                'msg' => $requestURI['msg'],
                'order' => $requestURI['order'],
                'guid' => $requestURI['guid']
            );
            if ($this->helper->getAuthChecksum($params) == $requestURI['hash']) {
                if (!isset($handlingSubject['payment']) || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface) {
                    throw new \InvalidArgumentException('Payment data object should be provided');
                }
                if ($requestURI['status'] == self::STATUS) {
                    $paymentDO = $handlingSubject['payment'];
                    $payment = $paymentDO->getPayment();
                    /** @var $payment \Magento\Sales\Model\Order\Payment */
                    $payment->setTransactionId($requestURI['guid']);
                    $payment->setIsTransactionClosed(false);
                }
            }
        }
    }
}
