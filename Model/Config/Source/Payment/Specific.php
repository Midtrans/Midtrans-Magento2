<?php

namespace Midtrans\Snap\Model\Config\Source\Payment;


use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Store\Model\ScopeInterface;

class Specific extends AbstractPayment
{
    const SPECIFIC_PAYMENT_CODE = 'specific';

    public $code = self::SPECIFIC_PAYMENT_CODE;

}
