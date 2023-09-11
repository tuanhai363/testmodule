<?php

namespace Magento\FlyingBluePayment\Controller\Order;

use Magento\Sales\Model\Order;
use Magento\FlyingBluePayment\Helper\Data;
use Magento\FlyingBluePayment\Model\Service\OrderService;
use Magento\FlyingBluePayment\Model\Service\ValidationService;
use Magento\FlyingBluePayment\Gateway\Curl;

/**
 * Class PlaceOrder
 */
class PlaceOrder extends \Magento\Framework\App\Action\Action
{

    const AUTH_PATH = 'checkout/oauth/token';
    const TRANSACTIONS_PATH = 'checkout/services/v2/transactions';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var ValidationService
     */
    protected $validationService;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var Magento\FlyingBluePayment\Helper\Data
     */
    protected $helper;
    /*
     * JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Class constructor
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Psr\Log\LoggerInterface                           $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Psr\Log\LoggerInterface $logger,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Sales\Api\Data\OrderInterface $order,
            \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
            Data $helper,
            OrderService $orderService,
            ValidationService $validationService,
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->scopeConfig       = $scopeConfig;
        $this->helper            = $helper;
        $this->logger            = $logger;
        $this->order             = $order;
        $this->messageManager    = $context->getMessageManager();
        $this->orderSender       = $orderSender;
        $this->orderService      = $orderService;
        $this->validationService = $validationService;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }

    public function execute()
    {
        try
        {
            $requestURI = $this->getRequest()->getPostValue();

            $result = $this->resultJsonFactory->create();

            if ($this->getRequest()->isAjax())
            {
                $quote = $this->_getCheckout()->getQuote();
                $this->validationService->validateQuote($quote);
                $quote->setReservedOrderId($requestURI['ppc_order_id']);

                if($quote->getCustomerEmail() === null
                    && $quote->getBillingAddress()->getEmail() !== null) {
                    $quote->setCustomerEmail($quote->getBillingAddress()->getEmail());
                }

                $orderData = $this->helper->createOrder($quote);

                $order = $this->order->loadByIncrementId($orderData['order_id']);

                $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT );
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT );
                $order->save();

                $requestParams = array(
                    'amount' => number_format((float)$this->helper->getOrderAmount(), 2, '.', ''),
                    'currency' => $this->helper->getCurrencyCode(),
                    'language' => 'en',
                    'merchant_code' => $this->helper->getMerchantCode(),
                    'merchant_order' => $orderData['order_id'],
                    'redirect_urls' => array(
                        'cancel' => $this->helper->getRedirectCancelUrl(),
                        'fail' => $this->helper->getRedirectFailUrl(),
                        'success' => $this->helper->getRedirectSuccessUrl()
                    ),
                    'timestamp' => (string)$this->helper->getTimeStamp(),
                    'type' => 'direct'
                );

                $transaction = $this->createTransaction($requestParams);
                
                if(!isset($transaction['links']))
                {
                    return $result->setData(array('success' => false , 'msg' => $transaction['message']));
                }

                $redirectURL = '';
                foreach($transaction['links'] as $res)
                {
                   if($res['method'] == 'REDIRECT')
                   {
                     $redirectURL = $res['href'];
                   }
                }

                if(empty($redirectURL))
                {
                    return $result->setData(array('success' => false , 'msg' => 'Something went wrong, please contact the administrator.'));
                }
                
                $this->deleteQuoteItems();

                return $result->setData(array('success' => true , 'url' => $redirectURL));
            }
        }
        catch (\Exception $e)
        {
            $this->logger->critical($e->getMessage());

            $this->messageManager->addExceptionMessage($e, $e->getMessage());

            return $this->_redirect('checkout/cart');

        }
    }
    
    public function deleteQuoteItems()
    {
        $checkoutSession = $this->_getCheckout();
        $allItems = $checkoutSession->getQuote()->getAllVisibleItems();

        foreach ($allItems as $item) {
            $itemId = $item->getItemId();
            $quoteItem=$this->getItemModel()->load($itemId);
            $quoteItem->delete();
        }
    }
    
    public function getItemModel(){
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $itemModel = $objectManager->create('Magento\Quote\Model\Quote\Item');
        return $itemModel;
    }

    public function createTransaction($order)
    {
        $auth = $this->getToken();

        if (!$auth) {
            return false;
        }

        if ($auth)
        {
            $url = $this->helper->getAPIURL() . 'checkout/services/v3/transactions';

            $requestParams = $order;

            $paramsJSON = json_encode($requestParams, JSON_UNESCAPED_SLASHES);

            $baseString  = $paramsJSON;

            $cert_path = $this->helper->getCertificate();

            $cert_store = file_get_contents($cert_path);

            $signature = '';

            if(!empty($cert_store))
            {
              openssl_pkcs12_read($cert_store, $cert_info, $this->helper->getPrivatekeyPassword());

              $privateKey = $cert_info['pkey'];

              $messageDigest = openssl_digest($baseString, 'sha256', true);

              openssl_private_encrypt($messageDigest, $encryptedData, $privateKey);
              $signature = base64_encode($encryptedData);
            }

            $headers = $this->getAuthHeaders($auth['access_token']);
            $headers[] = 'signature: '.$signature;

            $response =  $this->runCurl($url, $headers, 'POST', $paramsJSON);
            
            return $response;
        }

        return false;
    }

    private function runCurl($url, $header, $method = 'GET', $payload = '')
    {
        $ch            = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT,     true);
        curl_setopt($ch, CURLOPT_VERBOSE,         true);
        curl_setopt($ch, CURLOPT_HEADER,          true);

        $response_data = curl_exec($ch);
        $curl_info     = curl_getinfo($ch);

        $response_data = substr($response_data, $curl_info['header_size']);
        if (curl_errno($ch) && $this->helper->isDebug())
        {
            $this->helper->debugData('[ERROR]', 'cURL error: ' . curl_errno($ch), $url);
        }
        if($this->helper->isDebug())
        {
            $this->helper->debugData('[INFO-REQUEST]', print_r(json_decode($payload),true), $url);
            $this->helper->debugData('[INFO-RESPONSE]', print_r(json_decode($response_data),true), $url);
        }

        return json_decode($response_data, true);
    }

    private function getAuthHeaders($bearer = false)
    {
        $headers  = array("Content-type: application/x-www-form-urlencoded");
        $security = "Authorization: Basic " . $this->helper->getEncodedAuth();
        if($bearer)
        {
            $headers  = array("Content-type: application/json");
            $security = "Authorization: Bearer " . $bearer;
        }

        array_push($headers, $security);
        return $headers;
    }

    private function getToken()
    {
        $payload       = 'grant_type=client_credentials';
        $response_data = $this->runCurl($this->helper->getAPIURL() . 'checkout/oauth/token', $this->getAuthHeaders() , 'POST', $payload);

        if (isset($response_data['access_token']))
        {
            return $response_data;
        }
        else
        {
            return false;
        }
    }



    /**
     * @param CartInterface $quote
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateQuote($quote)
    {

        if (!$quote || !$quote->getItemsCount())
        {
            throw new InvalidArgumentException(__('We can\'t initialize checkout.'));
        }
    }
}
