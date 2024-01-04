<?php

namespace Pointspay\FlyingBluePayment\Controller\Payment;

use InvalidArgumentException;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Pointspay\FlyingBluePayment\Model\Service\OrderService;
use Pointspay\FlyingBluePayment\Model\Service\ValidationService;
use Magento\Sales\Model\Order;

class Cancel extends \Magento\Framework\App\Action\Action
{
    const STATUS = 'CANCELED';

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var ValidationService
     */
    protected $validationService;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param ValidationService $validationService
     * @param OrderService $orderService
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    public function __construct(
        Context $context,
        ValidationService $validationService,
        OrderService $orderService,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        parent::__construct($context);
        $this->messageManager = $context->getMessageManager();
        $this->validationService = $validationService;
        $this->orderService = $orderService;
        $this->order = $order;
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
     * @return ResponseInterface|void
     */
    public function execute()
    {
        try {
            $requestURI = $this->getRequest()->getParams();
            if (isset($requestURI['status']) && $requestURI['status'] == self::STATUS) {
                $orderId = $requestURI['order'];
                $order = $this->order->loadByIncrementId($orderId);
                $orderState = Order::STATE_CANCELED;
                $order->setState($orderState)->setStatus(Order::STATE_CANCELED);
                $order->addStatusToHistory(Order::STATE_CANCELED, $requestURI['msg']);
                $order->save();
                $lastorderid = $order->getEntityId();
                $lastquote = $order->getQuoteId();
                $this->_getCheckout()->setLastOrderId($lastorderid);
                $this->_getCheckout()->setLastRealOrderId($requestURI['order']);
                $this->_getCheckout()->setLastSuccessQuoteId($lastquote);
                $this->_getCheckout()->setLastQuoteId($lastquote);
                $orderItems = $order->getAllItems();
                $cart = $this->_objectManager->get('\Magento\Checkout\Model\Cart');
                $formKey = $this->_objectManager->get('\Magento\Framework\Data\Form\FormKey');
                foreach ($orderItems as $item) {
                    $qty = $item->getQtyOrdered();
                    $productId = $item->getProductId();
                    $params = array(
                        'form_key' => $formKey->getFormKey(),
                        'product' => $productId,
                        'qty' => $qty
                    );
                    $product = $this->_objectManager->get('\Magento\Catalog\Model\Product');
                    $_product = $product->load($productId);
                    $cart->addProduct($_product, $params);
                }
                $cart->save();
                throw new InvalidArgumentException(__($requestURI['msg']));
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
            return $this->_redirect('checkout/cart');
        }
    }
}
