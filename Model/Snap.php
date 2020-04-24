<?php

namespace Midtrans\Snap\Model;

use Midtrans\Snap\Model\Config\Source\Order\Status\Paymentreview;
use Midtrans\Snap\Model\Config\Source\Payment\AbstractPayment;

class Snap extends AbstractPayment
{
    const SNAP_PAYMENT_CODE = 'snap';
    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Payment code
     *
     * @var string
     */
    public $code = self::SNAP_PAYMENT_CODE;

    /**
     * Payment additional info block
     *
     * @var string
     */
    protected $_formBlockType = 'Midtrans\Snap\Block\Form\Snap';
}
