<?php

namespace Midtrans\Snap\Model\Config\Source\Payment;

class Installment extends AbstractPayment
{
    const INSTALLMENT_PAYMENT_CODE = 'installment';

    public $code = self::INSTALLMENT_PAYMENT_CODE;
}
