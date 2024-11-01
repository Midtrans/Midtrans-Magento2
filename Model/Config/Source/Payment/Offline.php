<?php

namespace Midtrans\Snap\Model\Config\Source\Payment;

class Offline extends AbstractPayment
{
    const OFFLINE_PAYMENT_CODE = 'offline';

    public $code = self::OFFLINE_PAYMENT_CODE;
}
