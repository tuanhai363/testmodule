<?php

namespace Magento\FlyingBluePayment\Model\Service;

use InvalidArgumentException;

class ValidationService
{

    /**
     * OrderService constructor.
     */
    public function __construct()
    {        
    }
    /**
     * @param CartInterface $quote
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateQuote($quote) {

        if (!$quote || !$quote->getItemsCount()) {
            throw new InvalidArgumentException(__('We can\'t initialize checkout.'));
        }
    }
}
