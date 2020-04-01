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
namespace Midtrans\Snap\Controller\Payment;

use Magento\Sales\Model\Order;

class Cancel extends AbstractAction
{
    public function execute()
    {
        $orderId = $this->getValue();
        $order = $this->_order->loadByIncrementId($orderId);
        if ($order->getState() == Order::STATE_NEW && !$order->hasInvoices()) {

            $order_note = "Midtrans | Payment Page close - by User";
            $this->cancelOrder($orderId, Order::STATE_CANCELED, $order_note);
            $this->unSetValue();
            return $this->resultRedirectFactory->create()->setPath('snap/index/close');
        } else {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
    }


}
