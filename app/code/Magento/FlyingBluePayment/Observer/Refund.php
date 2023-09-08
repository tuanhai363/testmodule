<?php

namespace Magento\FlyingBluePayment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\FlyingBluePayment\Model\Ui\ConfigProvider;

class Refund implements ObserverInterface
{
    const MODULE_NAME = 'Magento_FlyingBluePayment';
    protected $configProvider;

    /**
     * RequestBuilder constructor.
     * @param ConfigProvider $configProvider
     */
    public function __construct(
    ConfigProvider $configProvider
    )
    {
        //$this->setConfigProvider($configProvider);
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
       
    }
}
