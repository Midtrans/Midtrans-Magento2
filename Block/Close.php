<?php

namespace Midtrans\Snap\Block;

use \Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Close extends Template
{
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    public function cancelOrders()
    {
        return __('Your order has been canceled, because you close payment page. Thank you');
    }
}
