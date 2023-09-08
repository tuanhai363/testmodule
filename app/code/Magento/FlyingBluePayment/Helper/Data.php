<?php

namespace Magento\FlyingBluePayment\Helper;

use Magento\FlyingBluePayment\Logger\Logger;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Checkout\Model\Session;
use Exception;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const GATEWAY_URL      = 'https://secure.pointspay.com/';
    const GATEWAY_TEST_URL = 'https://uat-secure.pointspay.com/';
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var ObjectManager
     */
    protected $_objectManager;
    /**
     * @var Repository
     */
    protected $_assetRepo;

    protected $quoteManagement;

    public function __construct(
    Context $context, Config $config, StoreManagerInterface $storeManager , Logger $logger, ModuleListInterface $moduleList, ProductMetadataInterface $productMetadata, Session $checkoutSession, OrderFactory $orderFactory, \Magento\Framework\View\Asset\Repository $assetRepository, \Magento\Quote\Model\QuoteManagement $quoteManagement
    )
    {
        parent::__construct($context);

        $this->_moduleList = $moduleList;
        $this->productMetadata  = $productMetadata;
        $this->logger = $logger;
        $this->config           = $config;
        $this->storeManager     = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory    = $orderFactory;
        $this->objectManager    = ObjectManager::getInstance();
        $this->_assetRepo = $assetRepository;
        $this->quoteManagement = $quoteManagement;
    }

    public function createOrder($quote)
    {
        $quote->setPaymentMethod('flying_blue_payment');
        $quote->setInventoryProcessed(false);
        $quote->save();

        $quote->getPayment()->importData(['method' => 'flying_blue_payment']);
        $quote->collectTotals()->save();
        
        $orderdata = $this->quoteManagement->submit($quote);
        $orderdata->setEmailSent(1);
        if ($orderdata->getEntityId()) {
            $result['order_id'] = $orderdata->getRealOrderId();
        } else {
            $result = ['error' => 1, 'msg' => 'Your custom message'];
        }
        return $result;
  }

    /**
     * Log message as info.
     *
     * @param string|Exception $message
     * @param string|null $method Method or function name
     * @return string Completed message
     */
    public function logInfo($message, $method = null)
    {
        return $this->debugData('info', $message, $method);
    }

    /**
     * Log message as error.
     *
     * @param string|Exception $message
     * @param string|null $method Method or function name
     * @return string Completed message
     */
    public function logError($message, $method = null)
    {
        return $this->debugData('error', $message, $method);
    }

    /**
     * Log message.
     *
     * @param string $type
     * @param string|Exception $message
     * @param string|null $method Method or function name
     * @return string Completed message
     */
    public function debugData($type, $message, $method = null)
    {
        //log information about the environment
        $phpVersion        = explode('-', phpversion())[0];
        $extendedDebugData = array(
            '[PHP version] ' . $phpVersion,
            '[Magento version] ' . $this->getMagentoVersion(),
            '[Flying Blue+ plugin version] ' . $this->getExtensionVersion(),
        );
        foreach ($extendedDebugData as &$param)
        {
            $param = PHP_EOL . "\t\t" . $param;
        }

        $type = strtoupper($type);

        if ($message instanceof Exception)
        {
            $message = 'Exception thrown with message: ' . $message->getMessage();
        }

        $message = ucfirst($message);

        if (is_string($method))
        {
            $message = sprintf('In %s: %s', $method, $message);
        }

        $debugLine = sprintf('[%s] %s', $type, $message);

        if ($this->isDebug() && $message)
        {
            $this->logger->debug(sprintf('%s', implode('', $extendedDebugData)));
            $this->logger->debug($debugLine);
        }

        return $debugLine;
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return (boolean) $this->scopeConfig->getValue('payment/flyingblue/debug');
    }

    /**
     * @return boolean
     */
    public function isTestMode()
    {
        return (boolean) $this->scopeConfig->getValue('payment/flyingblue/test_mode');
    }

    /**
     * @return string
     */
    public function getMerchantCode()
    {
        return $this->scopeConfig->getValue('payment/flyingblue/merchant_code');
    }

    /**
     * @return string
     */
    public function getCertificate()
    {
        return $this->scopeConfig->getValue('payment/flyingblue/certificate');
    }

    /**
     * @return string
     */
    public function getPrivatekeyPassword()
    {
        return $this->scopeConfig->getValue('payment/flyingblue/privatekey_password');
    }
    /**
     * Returns PointsPay's logo center URL
     *
     * @return string
     */
    public function getLogoCenterUrl()
    {
        $asset = $this->_assetRepo->createAsset('Magento_FlyingBluePayment::img');

        return $asset->getUrl();
    }
    /**
     * Returns the image URL in PointsPay Logo
     *
     * @param string $imageName
     * @param string $extension
     *
     * @return string
     */
    public function getLogoCenterImageUrl($imageName = null, $extension = null)
    {
        if(!is_null($imageName)) {
            $_imageFullName = is_null($extension) ? $imageName : implode('.', array($imageName, $extension));
            return implode('/', array($this->getLogoCenterUrl(), $_imageFullName));
        }
        return null;
    }
    /**
     *
     * @return string
     */
    public function getPaymentDisabledDesc()
    {
        return sprintf(__('PAYMENT_DISBALED_DESCRIPTION'), $this->storeManager->getStore()->getWebsite()->getName());
    }

    /**
     * @return string
     */
    public function getAPIUsername()
    {
        return $this->scopeConfig->getValue('payment/flyingblue/api_username');
    }

    /**
     * @return string
     */
    public function getAPIPassword()
    {
        return $this->scopeConfig->getValue('payment/flyingblue/api_password');
    }

    public function getEncodedAuth()
    {
        return base64_encode($this->getAPIUsername() . ':' . $this->getAPIPassword());
    }

    /**
     * @return string
     */
    public function getAPIAccessToken()
    {
        return $this->scopeConfig->getValue('payment/flyingblue/api_access_token');
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        if ($this->isTestMode())
        {
            return self::GATEWAY_TEST_URL;
        }

        return self::GATEWAY_URL;
    }

    /**
     * @return string
     */
    public function getMerchantTID()
    {
        $this->_checkoutSession->getQuote()->reserveOrderId();
        return substr(md5(time() . serialize($this->_checkoutSession->getQuote()->getReservedOrderId())), 0, 11);
    }

    /**
     * @return string
     */
    public function getOrderAmount()
    {
        $cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');

        return $cart->getQuote()->getGrandTotal();
    }

    /**
     * @return string
     */
    public function getTimeStamp()
    {
        return intval(microtime(true) * 1000);
    }

    public function getReservedOrderID()
    {
        return $this->_checkoutSession->getQuote()->getReservedOrderId();
    }

    /**
     * @return string
     */
    public function getOrderID()
    {
        $this->_checkoutSession->getQuote()->reserveOrderId();
        return $this->_checkoutSession->getQuote()->getReservedOrderId();
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {

        $currency = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface');

        return $currency->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Returns Magento Config instance.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns the URL where customers are redirected on success
     *
     * @return string
     */
    public function getRedirectSuccessUrl()
    {
        return $this->storeManager->getStore()->getUrl('flyingblue/payment/success');
    }
    /**
     * Returns the URL to reload auth checksum controller
     *
     * @return string
     */
    public function getReloadAuthChecksumUrl()
    {
        return $this->storeManager->getStore()->getUrl('flyingblue/authChecksum/reloadAuthChecksum');
    }
    /**
     * Returns the URL to reload auth checksum controller
     *
     * @return string
     */
    public function getUpdatedOrderDataRequestURL()
    {
        return $this->storeManager->getStore()->getUrl('flyingblue/quote/getCurrentOrderData');
    }
    /**
     * Returns the URL to place order controller
     *
     * @return string
     */
    public function getPlaceOrderRequestURL()
    {
        return $this->storeManager->getStore()->getUrl('flyingblue/order/placeOrder');
    }
    /**
     * Returns the URL where customers are redirected on fail
     *
     * @return string
     */
    public function getRedirectFailUrl()
    {
        return $this->storeManager->getStore()->getUrl('flyingblue/payment/failure');
    }
    /**
     * Returns the URL where customers are redirected on cancel
     *
     * @return string
     */
    public function getRedirectCancelUrl()
    {
        return $this->storeManager->getStore()->getUrl('flyingblue/payment/cancel');
    }

    /**
     * Returns the extension version.
     *
     * @return string
     */
    public function getExtensionVersion()
    {
        $moduleCode = 'flyingbluepaymentmethod';
        $moduleInfo = $this->_moduleList->getOne($moduleCode);
        if (isset($moduleInfo)) {
            return $moduleInfo['setup_version'];
        }
        else {
            return '0.0'; # TODO: temporary workaround, it shouldn't be important
        }
    }

    /**
     * Returns the Magento version.
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Returns the authorization checksum.
     *
     * @return string
     */
    public function getAuthChecksum($params)
    {

        $md5_value = '';
        foreach ($params as $k => $v)
        {
            $md5_value .= $v;
        }
        //return $md5_value. $this->getToken() ;
        $hash_hmac_value = hash_hmac('md5', $md5_value, $this->getAPIAccessToken());

        return $hash_hmac_value;
    }
}
