<?php

namespace Magento\FlyingBluePayment\Controller\Quote;

use InvalidArgumentException;
use \Magento\FlyingBluePayment\Gateway\Config\Config;

class GetCurrentOrderData extends \Magento\Framework\App\Action\Action
{
    /**
     * @var MessageManager
     */
    protected $messageManager;
    /**
     * @var Config
     */
    protected $config;
    /*
     * JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(\Magento\Framework\App\Action\Context $context, Config $config , \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory)
    {
        $this->messageManager = $context->getMessageManager();
        $this->config           = $config;
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

    /**
     * Handle callback from PointsPay
     *
     * @return string
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()) 
        {
            $ppc_amount =  $this->config->getOrderAmount();
            $ppc_order_id =  $this->config->getOrderID();
            if(!$ppc_amount || !$ppc_order_id)
                return $result->setData(array('error'=>__('We can\'t initialize checkout.')));
            
            return $result->setData(array('ppc_amount'=>$ppc_amount, 'ppc_order_id' => $ppc_order_id));
        }        
    }
}
