<?php
/**
 *
 *        c.                                            c.  :.
 *        E1                                            E.  !)
 * ::.    E1    !3.        ,5"`'\.;F'`"t.   i.   cF'`'=.E.  !7''`   ;7""   '""!.    ;7"'"!.   ;7'`'*=
 * ::.    E1    !3.        t.    !)     t   i.  tL      t.  !)     !)     ,,...;)  :1     I.  !t.,
 * ::.    E1    !3.        t.    !)     t   i.  E.      E.  !)     !L    t'    :1  :1     !)    ``"1.
 * ::.    E1    !3.        t.    !)     t   i.  '1.,  ,ct.  !1,    !L    1.  ,;31  :1     !) -..   ;7
 * '      E1    `'         `            `   `     ``'`  `    `'``  `      `''`  `   `     `    ``'`
 *        E7
 *
 * Midtrans Snap Magento 2 Module
 *
 * Copyright (c) 2020 Midtrans PT.
 * This file is open source and available under the MIT license.
 * See the LICENSE file for more info.
 *
 */
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
