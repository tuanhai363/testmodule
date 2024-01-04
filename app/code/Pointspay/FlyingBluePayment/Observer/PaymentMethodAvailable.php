<?php

namespace Pointspay\FlyingBluePayment\Observer;

use Pointspay\FlyingBluePayment\Helper\Data;
use Magento\Framework\Event\ObserverInterface;
use Pointspay\FlyingBluePayment\Model\Ui\ConfigProvider;

class PaymentMethodAvailable implements ObserverInterface
{
    protected $helper;

    /**
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($observer->getEvent()->getMethodInstance()->getCode() == ConfigProvider::CODE) {
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', $this->helper->isEnabled());
        }
    }
}
