<?php

namespace Midtrans\Snap\Model\Config\Source\Payment;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Store\Model\ScopeInterface;

class Offline extends AbstractPayment
{
    const OFFLINE_PAYMENT_CODE = 'offline';
    public $code = self::OFFLINE_PAYMENT_CODE;
}
