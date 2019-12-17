<?php

namespace Midtrans\Snap\Block;
use \Magento\Framework\View\Element\Template;


class Close extends Template
{
    public function __construct(\Magento\Framework\View\Element\Template\Context $context)
    {
        parent::__construct($context);
    }

    public function cancelOrders()
    {
        return __('Your order has been canceled, because you close payment page. Thank you');
    }

}