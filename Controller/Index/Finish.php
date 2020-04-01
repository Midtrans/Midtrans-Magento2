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

namespace Midtrans\Snap\Controller\Index;

use Exception;
use Magento\Framework\View\Result\Page;

use Midtrans\Snap\Controller\Payment\AbstractAction;
use Midtrans\Snap\Gateway\Transaction;
use Midtrans\Snap\Gateway\Config\Config;


class Finish extends AbstractAction
{

    public function execute()
    {

        try {
            $transactionId = $this->getRequest()->getParam('id');
            $orderId = $this->getRequest()->getParam('order_id');
            if ($transactionId != null) {
                $param = $transactionId;
                $order = $this->getOrderByTransactionId($param);
            } else if ($orderId != null) {
                $param = $orderId;
                $order = $this->getQuoteByOrderId($param);
            } else {
                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }

            if (isset($order)) {
                if ($order->getPayment() != null) {
                    $paymentCode = $order->getPayment()->getMethod();
                } else {
                    return $this->resultRedirectFactory->create()->setPath('checkout/cart');
                }
            }

            Config::$isProduction = $this->data->isProduction();
            if (isset($paymentCode)) {
                Config::$serverKey = $this->data->getServerKey($paymentCode);
            }

            $transaction = new Transaction();
            $statusResult = $transaction::status($param);

            $orderId = $statusResult->order_id;
            $amount = $statusResult->gross_amount;
            $transaction = $statusResult->transaction_status;
            $payment_type = $statusResult->payment_type;

            $this->registry->register('amount', $amount, false);
            $this->registry->register('transaction_status', $transaction, false);
            $this->registry->register('payment_type', $payment_type, false);
            $this->registry->register('order_id', $orderId, false);

        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->_midtransLogger->midtransError($e->getMessage());
        }

        /** @var Page $resultPage */
        $resultPage = $this->_pageFactory->create();
        return $resultPage;
    }
}
