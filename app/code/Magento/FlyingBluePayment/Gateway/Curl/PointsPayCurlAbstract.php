<?php

namespace Magento\FlyingBluePayment\Gateway\Curl;

class PointsPayCurlAbstract
{
    const AUTH_PATH = 'checkout/oauth/token';
    const TRANSACTIONS_PATH = '/checkout/services/v2/transactions';  
    
    protected $helper;
    
    public function __construct($helper)
    {
        $this->helper = $helper;
    }
}

