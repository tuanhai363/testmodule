<?php

namespace Magento\FlyingBluePayment\Gateway\Config;

use Magento\FlyingBluePayment\Helper\Data;

class Config
{
    /**
     * @var Data
     */
    protected $helper;
    
    protected $tid = NULL;
    protected $timestamp = NULL;
    protected $orderid = NULL;
    protected $paymentMarkSize = NULL;


    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Determines if debug mode is enabled.
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->helper->isDebug();
    }
    
    /**
     * Determines if debug mode is enabled.
     *
     * @return bool
     */
    public function isTestMode()
    {
        return $this->helper->isTestMode();
    }
    /**
     * Returns the merchant order ID for client-side functionality.
     *
     * @return string
     */
    public function getOrderID()
    {
        if($this->orderid == NULL) 
            return $this->helper->getOrderID();
        
        return $this->orderid;
    }
    
    /**
     * Get PointsPay "mark" image URL
     * Supposed to be used on payment methods selection
     * $staticSize is applicable for static images only
     *
     * @param string $localeCode
     * @param float $orderTotal
     * @param string $pal
     * @param string $staticSize
     *
     * @return string
     */
    public function getPaymentMarkImageUrl($localeCode)
    {       
        return $this->helper->getLogoCenterImageUrl('logo-pp-checkout', 'svg');
    }
    
    public function getUrlImgLogo()
    {       
        return $this->helper->getLogoCenterImageUrl('btn-img', 'png');
    }
    
    public function getPaymentDisabledDesc()
    {       
        return $this->helper->getPaymentDisabledDesc();
    }
    
    /**
     * Returns the redirect Urls for client-side functionality.
     *
     * @return string
     */
    public function getRedirectURLs()
    {
        return array(
            'success' => $this->helper->getRedirectSuccessUrl(),
            'failure' => $this->helper->getRedirectFailUrl(),
            'cancel' => $this->helper->getRedirectCancelUrl(),
        );
    }
    
    public function getReloadAuthChecksumURL()
    {
        return $this->helper->getReloadAuthChecksumUrl();        
    }
    
    public function getUpdatedOrderDataRequestURL()
    {
        return $this->helper->getUpdatedOrderDataRequestURL();        
    }
    
    public function getPlaceOrderRequestURL()
    {
        return $this->helper->getPlaceOrderRequestURL();        
    }

    /**
     * Returns the merchant order amount for client-side functionality.
     *
     * @return string
     */
    public function getOrderAmount()
    {
        return $this->helper->getOrderAmount();
    }

    /**
     * Returns the time stamp for client-side functionality.
     *
     * @return string
     */
    public function getTimeStamp()
    {
        if($this->timestamp == NULL)
            return $this->helper->getTimeStamp();
        
        return $this->timestamp;
    }

    /**
     * Returns the checksum for client-side functionality.
     *
     * @return string
     */
    public function getAuthChecksum(array $post)
    {
        return $this->helper->getAuthChecksum($post);
    }

    /**
     * Returns the merchant transaction ID for client-side functionality.
     *
     * @return string
     */
    public function getTID()
    {
        if($this->tid == NULL)                
            return $this->helper->getMerchantTID();
        
        return $this->tid;
    }

    /**
     * Returns the currency code for client-side functionality.
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->helper->getCurrencyCode();
    }

    public function getMerchantCode()
    {
        return $this->helper->getMerchantCode();
    }

    public function getApiUrl()
    {
        return $this->helper->getApiurl();
    }
}
