<?php

namespace Midtrans\Snap\Block;

use Magento\Framework\Registry;
use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;

class Finish extends Template
{
    protected $registry;
    public function __construct(
        Context $context,
        Registry $registry
    )
    {
        parent::__construct($context);
        $this->registry = $registry;
    }

    public function getDataTransaction() {
        $transaction['gross_amount'] = $this->registry->registry('amount');
        $transaction['status'] = $this->registry->registry('transaction_status');
        $transaction['payment_type'] = $this->registry->registry('payment_type');
        $transaction['gross_amount'] = $this->registry->registry('amount');
        $transaction['order_id'] = $this->registry->registry('order_id');

        return $transaction;
    }
}
