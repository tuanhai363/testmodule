<?php

namespace Magento\FlyingBluePayment\Controller\Payment;

use Magento\Sales\Model\Order;
use Magento\FlyingBluePayment\Helper\Data;
use InvalidArgumentException;
use Magento\FlyingBluePayment\Model\Service\OrderService;
use Magento\FlyingBluePayment\Model\Service\ValidationService;

class Success extends \Magento\Framework\App\Action\Action
{
    const STATUS = 'SUCCESS';
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

    /**
     * Class constructor
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Psr\Log\LoggerInterface                           $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Psr\Log\LoggerInterface $logger, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Sales\Api\Data\OrderInterface $order, \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender, Data $helper, OrderService $orderService, ValidationService $validationService
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
        parent::__construct($context);
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }

    /**
     * Handle callback from PointsPay
     *
     * @return string
     */
    public function execute()
    {
        try
        {

            $requestURI = $this->getRequest()->getParams();

            $params = array(
                'status' => $requestURI['status'],
                'msg'    => $requestURI['msg'],
                'order'  => $requestURI['order'],
                'guid'   => $requestURI['guid'],
            );

            if ($this->helper->getAuthChecksum($params) == $requestURI['hash'])
            {
                if (isset($requestURI['status']) && $requestURI['status'] == self::STATUS)
                {
                    $orderId = $requestURI['order'];
                    $order = $this->order->loadByIncrementId($orderId);

                    $lastorderid = $order->getEntityId();
                    $lastquote = $order->getQuoteId();

                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);

                    $payment     = $order->getPayment();
                    $payment->capture();
                    $payment->setLastTransId($requestURI['guid']);
                    $payment->setTransactionId($requestURI['guid']);

                    $trans       = $this->_objectManager->get('Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface');
                    $transaction = $trans->setPayment($payment)
                            ->setOrder($order)
                            ->setTransactionId($requestURI['guid'])
                            ->setFailSafe(true)
                            //build method creates the transaction and returns the object
                            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

                    $payment->setParentTransactionId(null);
                    $payment->save();
                    $order->save();
                    $transaction->save();

                    $this->_getCheckout()->setLastSuccessQuoteId($lastquote);
                    $this->_getCheckout()->setLastQuoteId($lastquote);
                    $this->_getCheckout()->setLastOrderId($lastorderid);
                    $this->_getCheckout()->setLastRealOrderId($requestURI['order']);
                    
                    $this->deleteQuoteItems();

                    $this->_redirect('checkout/onepage/success', ['_secure' => TRUE]);

                }
                else
                {
                    $this->logger->debug('Status mismatch.');
                    throw new InvalidArgumentException(__('We can\'t initialize checkout.'));
                }
            }
            else
            {
                $this->logger->debug('Hash mismatch.');
                throw new InvalidArgumentException(__('We can\'t initialize checkout.'));
            }
        }
        catch (\Exception $e)
        {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
            $this->logger->critical($e->getMessage());

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
