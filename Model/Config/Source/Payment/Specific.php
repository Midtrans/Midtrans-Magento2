<?php

namespace Midtrans\Snap\Model\Config\Source\Payment;

class Specific extends AbstractPayment
{
    const SPECIFIC_PAYMENT_CODE = 'specific';

    public $code = self::SPECIFIC_PAYMENT_CODE;
}
