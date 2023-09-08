<?php

namespace PointsPay\PointsPay\Model;

/**
 * Pay In Store payment method model
 */
class FlyingBluePaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'flyingbluepaymentmethod';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isGateway                 = true;
    protected $_canOrder                  = true;
    protected $_canAuthorize              = true;
    protected $_canCapture                = true;
    protected $_canCapturePartial         = false;
    protected $_canRefund                 = true;
    protected $_canRefundInvoicePartial   = true;
    protected $_canVoid                   = false;
    protected $_canHold                   = false;
    protected $_canUseInternal            = false;
    protected $_canUseCheckout            = true;
    protected $_canUseForMultishipping    = false;
    protected $_canFetchTransactionInfo   = false;
    protected $_canCreateBillingAgreement = false;
    protected $_canReviewPayment          = true;

    public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory, \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory, \Magento\Payment\Helper\Data $paymentData, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Payment\Model\Method\Logger $logger, \Magento\Framework\Module\ModuleListInterface $moduleList, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Magento\Directory\Model\CountryFactory $countryFactory, array $data = array()
    )
    {
        parent::__construct(
                $context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $moduleList, $localeDate, null, null, $data
        );
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canAuthorize())
        {
            throw new \Magento\Framework\Exception\LocalizedException(__('The authorize action is not available.'));
        }
        
        return $this;
    }

    /**
     * Capture payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canCapture())
        {
            throw new \Magento\Framework\Exception\LocalizedException(__('The capture action is not available.'));
        }

        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canRefund())
        {
            throw new \Magento\Framework\Exception\LocalizedException(__('The refund action is not available.'));
        }

        return $this;
    }
}
