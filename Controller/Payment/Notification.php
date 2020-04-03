<?php

namespace Midtrans\Snap\Controller\Payment;

use Magento\Sales\Model\Order;
use Midtrans\Snap\Gateway\Config\Config;
use \Midtrans\Snap\Gateway\Notification as MidtransNotification;

/**
 * Class Notification
 * Handle notifications from midtrans http notifications
 *
 * @package Midtrans\Snap\Controller\Payment
 */
class Notification extends AbstractAction
{

    public function execute()
    {
        $input_source = "php://input";
        $body = json_decode(file_get_contents($input_source), true);
        $orderIdRequest = $body['order_id'];
        $bodyOrder = $this->getQuoteByOrderId($orderIdRequest);
        /**
         * Do not process if order not found,
         * if Log enable, add record to /var/log/midtrans/notification.log
         */
        if ($bodyOrder->isEmpty()) {
            $_info = "404 NOT FOUND - Order with orderId: " . $orderIdRequest . " not found on Magento 2";
            $this->_midtransLogger->midtransNotification($_info);
            echo "404 Order Not found";
            exit();
        }

        $paymentCode = $bodyOrder->getPayment()->getMethod();

        Config::$serverKey = $this->getData()->getServerKey($paymentCode);
        Config::$isProduction = $this->getData()->isProduction();

        $notif = new MidtransNotification();
        $orderId = $notif->order_id;
        $order = $this->getQuoteByOrderId($orderId);

        $transaction = $notif->transaction_status;
        $trxId = $notif->transaction_id;
        $fraud = $notif->fraud_status;
        $payment_type = $notif->payment_type;

        $note_prefix = "MIDTRANS NOTIFICATION  |  ";
        if ($transaction == 'capture') {
            if ($fraud == 'challenge') {
                $order_note = $note_prefix . 'Payment status challenged. Please take action on your Merchant Administration Portal - ' . $payment_type;
                $this->setOrderStateAndStatus($orderId, Order::STATE_PAYMENT_REVIEW, $order_note, $trxId);
            } else if ($fraud == 'accept') {
                $order_note = $note_prefix . 'Payment Completed - ' . $payment_type;
                if ($order->canInvoice() && !$order->hasInvoices()) {
                    $this->generateInvoice($orderId, $payment_type);
                }
                $this->setOrderStateAndStatus($orderId,Order::STATE_PROCESSING, $order_note, $trxId);
            }
        } else if ($transaction == 'settlement') {
            if ($payment_type != 'credit_card') {
                $order_note = $note_prefix . 'Payment Completed - ' . $payment_type;
                if ($order->canInvoice() && !$order->hasInvoices()) {
                    $this->generateInvoice($orderId, $payment_type);
                }
                //$order->setData('state', 'processing');
               // $order->setStatus(Order::STATE_PROCESSING);
               // $order->setState(Order::STATE_PROCESSING);
                $this->setOrderStateAndStatus($orderId, Order::STATE_PROCESSING, $order_note, $trxId);
                //$order->addCommentToStatusHistory($note_prefix . 'Payment Completed - ' . $payment_type, false, false);
            }
        } else if ($transaction == 'pending') {
            $order_note = $note_prefix . 'Awating Payment - ' . $payment_type;
            $this->setOrderStateAndStatus($orderId, Order::STATE_PENDING_PAYMENT, $order_note, $trxId);
        } else if ($transaction == 'cancel' ) {
            if ($order->canCancel()) {
                $order_note = $note_prefix . 'Canceled Payment - ' . $payment_type;
                $this->cancelOrder($orderId, Order::STATE_CANCELED, $order_note);
            }
        } else if ($transaction == 'expire') {
            if ($order->canCancel()) {
                $order_note = $note_prefix . 'Expired Payment - ' . $payment_type;
                $this->cancelOrder($orderId, Order::STATE_CANCELED, $order_note);
            }
        } else if ($transaction == 'deny') {
            if ($order->canCancel()) {
                $order->setStatus(Order::STATE_CANCELED);
                $order->addCommentToStatusHistory($note_prefix . 'Payment Deny - ' . $payment_type, false, false);
            }
        } else if ($transaction == 'refund' || $transaction == 'partial_refund') {
            $isFullRefund = ($transaction == 'refund') ? true : false;

            /**
             * Get last array object from refunds array and get the value from last refund object
             */
            $refunds = $notif->refunds;
            $refund[] = end($refunds);
            $refund_key = $refund[0]->refund_key;
            $refund_amount = $refund[0]->refund_amount;
            $refund_reason = $refund[0]->reason;

            /**
             * Do not process if the notification contain 'bank_confirmed_at' from request body
             */
            $refundRaw[] = end($body['refunds']);
            if (isset($refundRaw[0]['bank_confirmed_at'])) {
                echo 'OK';
                exit;
            }

            /**
             * Handle fullRefund: if refunded from midtrans dashboard close order and add comment history ,
             * If refund from magento dashboard add only comment history.
             */
            if ($isFullRefund) {
                $refund_note = $note_prefix . 'Full Refunded: ' . $refund_amount . '  |  Refund-Key: '.$refund_key.'  |  Reason: ' . $refund_reason;
                if ($order->getStatus() != Order::STATE_CLOSED || $order->getState() != Order::STATE_CLOSED && $this->canFullRefund($refund_key, $order, $refund_amount) == true) {
                    $this->cancelOrder($orderId, Order::STATE_CLOSED, $refund_note);
                } else {
                    $order->addCommentToStatusHistory($refund_note, false, false);
                }
            }

            /**
             * Handle partial refund from midtrans dashboard to add comment history
             */
            if (!$isFullRefund && $order->getStatus() === Order::STATE_PROCESSING) {
                $partialRefundNote = $note_prefix . 'Partial Refunded: ' . $refund_amount . '  |  Refund-Key: '.$refund_key.'  |  Reason: ' . $refund_reason;
                $order->addCommentToStatusHistory($partialRefundNote, false, false);
            }
        }
        $this->saveOrder($order);

        /**
         * If log request isEnabled, add request payload to var/log/midtrans/request.log
         */
        $_info = "status : " . $transaction . " , order : " . $orderId . ", payment type : " . $payment_type;
        $this->_midtransLogger->midtransNotification($_info);
        echo 'OK';
    }
}
