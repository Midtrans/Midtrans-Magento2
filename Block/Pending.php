<?php

namespace Midtrans\Snap\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Pending extends Template
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    public function unpaidOrders()
    {
        return __('We have receive your order but is currently awaiting your payment. Once we received the payment for your order, it will be completed.
        If you have already provided payment details then we will process your order.');
    }

    public function getAdditionalInfoHtml()
    {
        return $this->_layout->renderElement('order.success.additional.info');
    }
}
