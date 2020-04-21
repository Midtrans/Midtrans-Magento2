<?php

namespace Midtrans\Snap\Model;

use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Midtrans\Snap\Model\Config\Source\Order\Status\Paymentreview;
use Magento\Sales\Model\Order;
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

//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_isOffline = true;

    /**
     * Payment additional info block
     *
     * @var string
     */
    protected $_formBlockType = 'Midtrans\Snap\Block\Form\Snap';

}
