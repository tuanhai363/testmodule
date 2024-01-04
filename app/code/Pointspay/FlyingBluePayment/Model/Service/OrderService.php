<?php

namespace Pointspay\FlyingBluePayment\Model\Service;

use Magento\Quote\Model\Quote;
use Magento\Checkout\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Pointspay\FlyingBluePayment\Model\Ui\ConfigProvider;

class OrderService
{
    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var AgreementsValidatorInterface
     */
    protected $agreementsValidator;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Data
     */
    protected $checkoutHelper;

    /**
     * OrderService constructor.
     * @param CartManagementInterface $cartManagement
     * @param Session $customerSession
     * @param Data $checkoutHelper
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        Session $customerSession,
        Data $checkoutHelper
    ) {
        $this->cartManagement = $cartManagement;
        $this->customerSession = $customerSession;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * @param Quote $quote
     * @param $cardToken
     * @param array $agreement
     * @throws LocalizedException
     */
    public function execute(Quote $quote)
    {
        if ($this->getCheckoutMethod($quote) === Onepage::METHOD_GUEST) {
            $this->prepareGuestQuote($quote);
        }
        $payment = $quote->getPayment();
        $payment->setMethod(ConfigProvider::CODE);
        $this->disabledQuoteAddressValidation($quote);
        $quote->collectTotals();
        $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * Get checkout method
     *
     * @param Quote $quote
     * @return string
     */
    private function getCheckoutMethod(Quote $quote)
    {
        if ($this->customerSession->isLoggedIn()) {
            return Onepage::METHOD_CUSTOMER;
        }
        if (!$quote->getCheckoutMethod()) {
            if ($this->checkoutHelper->isAllowedGuestCheckout($quote)) {
                $quote->setCheckoutMethod(Onepage::METHOD_GUEST);
            } else {
                $quote->setCheckoutMethod(Onepage::METHOD_REGISTER);
            }
        }
        return $quote->getCheckoutMethod();
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @param Quote $quote
     * @return void
     */
    private function prepareGuestQuote(Quote $quote)
    {
        $quote->setCustomerId(0)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail()) // issue -> magento do not provide email address for guest order
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
    }

    /**
     * @param Quote $quote
     */
    protected function disabledQuoteAddressValidation(Quote $quote)
    {
        $billingAddress = $quote->getBillingAddress();
        $billingAddress->setShouldIgnoreValidation(true);
        if (!$quote->getIsVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setShouldIgnoreValidation(true);
            if (!$billingAddress->getEmail()) {
                $billingAddress->setSameAsBilling(1);
            }
        }
    }

}
