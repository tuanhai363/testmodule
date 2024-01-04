<?php

namespace Pointspay\FlyingBluePayment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Pointspay\FlyingBluePayment\Model\Ui\ConfigProvider;

class Refund implements ObserverInterface
{
    const MODULE_NAME = 'Pointspay_FlyingBluePayment';

    protected $configProvider;

    /**
     * RequestBuilder constructor.
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
    }
}
