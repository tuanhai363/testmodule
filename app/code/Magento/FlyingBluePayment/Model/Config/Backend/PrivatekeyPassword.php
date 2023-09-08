<?php

namespace Magento\FlyingBluePayment\Model\Config\Backend;

use Magento\FlyingBluePayment\Helper\Data;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class PrivatekeyPassword extends Value
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * PairingCode constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param ManagerInterface $messageManager
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
    Context $context, Registry $registry, ScopeConfigInterface $config, TypeListInterface $cacheTypeList, AbstractResource $resource = null, AbstractDb $resourceCollection = null, ManagerInterface $messageManager, Data $helper, array $data = [])
    {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);

        $this->messageManager = $messageManager;
        $this->helper         = $helper;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        $PrivatekeyPassword = trim((string) $this->getValue());


        if ($PrivatekeyPassword === '')
        {
            return;
        }
        try
        {
            $this->helper->getConfig()->saveConfig('payment/flyingblue/privatekey_password', $PrivatekeyPassword, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        }
        catch (\Exception $e)
        {
            $this->helper->logError($e, __METHOD__);
            $this->messageManager->addErrorMessage('There was an error while trying to save privatekey password.');

            return;
        }
        //$this->helper->logInfo('Attempting to pair with BitPay with pairing code ' . $pairingCode, __METHOD__);
    }
}
