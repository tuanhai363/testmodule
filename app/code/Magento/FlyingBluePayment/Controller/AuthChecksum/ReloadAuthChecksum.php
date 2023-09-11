<?php

namespace Magento\FlyingBluePayment\Controller\AuthChecksum;

use InvalidArgumentException;
use \Magento\FlyingBluePayment\Gateway\Config\Config;

class ReloadAuthChecksum extends \Magento\Framework\App\Action\Action
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
            $ppc_merchant_transaction_id = $this->getRequest()->getParam('ppc_merchant_transaction_id');
            $ppc_merchant_code = $this->getRequest()->getParam('ppc_merchant_code');
            $ppc_merchant_order = $this->getRequest()->getParam('ppc_merchant_order');
            $ppc_currency_code = $this->getRequest()->getParam('ppc_currency_code');
            $ppc_amount = $this->getRequest()->getParam('ppc_amount');
            $ppc_timestamp = $this->getRequest()->getParam('ppc_timestamp');
            

            $authorization_checksum = $this->config->getAuthChecksum(array(
                        'ppc_merchant_transaction_id' => $ppc_merchant_transaction_id,
                        'ppc_merchant_code'           => $ppc_merchant_code,
                        'ppc_merchant_order'          => $ppc_merchant_order,
                        'ppc_currency_code'           => $ppc_currency_code,
                        'ppc_amount'                  => $ppc_amount,
                        'ppc_language'                => 'en',
                        'ppc_timestamp'               => $ppc_timestamp
                    ));

            
            return $result->setData(array('authorization_checksum'=>$authorization_checksum));
        }        
    }
}
