<?php

namespace Midtrans\Snap\Block\Form;

use Magento\Payment\Block\Form;

class Snap extends Form
{
    /**
     * Instructions text
     *
     * @var string
     */
    protected $_instructions;

    protected $_template = 'form/snap.phtml';
}
