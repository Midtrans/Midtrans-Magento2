<?php

namespace Midtrans\Snap\Controller\Index;

use Exception;
use Midtrans\Snap\Controller\Payment\Action;
use Midtrans\Snap\Gateway\Config\Config;
use Midtrans\Snap\Gateway\Transaction;

class Finish extends Action
{
    const PAYMENT_CODE = 'snap';

    public function execute()
    {
        $orderIdRequest = $this->getRequest()->getParam('order_id');
        $midtransResult = null;

        try {
            /* Handle for BCA Klikpay */
            $transactionId = $this->getRequest()->getParam('id');
            if ($transactionId != null) {
                Config::$isProduction = $this->midtransDataConfiguration->isProduction();
                Config::$serverKey = $this->midtransDataConfiguration->getServerKey(self::PAYMENT_CODE);
                $transaction = new Transaction();
                $midtransResult = $transaction::status($transactionId);
            }
            /* Handle for direct debit, cardless credit, gopay, cc */

            elseif ($this->getRequest()->getPostValue() != null) {
                $postValue = $this->getRequest()->getPostValue();
                $response = $postValue['response'];
                $decoded_response = $this->midtransDataConfiguration->json->unserialize($response);
                $orderIdRequest = $decoded_response['order_id'];

                if (strpos($orderIdRequest, 'multishipping-') !== false) {
                    // 2. Finish for multishipping
                    $quoteId = str_replace('multishipping-', '', $orderIdRequest);
                    $incrementIds = $this->paymentOrderRepository->getIncrementIdsByQuoteId($quoteId);

                    foreach ($incrementIds as $key => $id) {
                        $order  = $this->paymentOrderRepository->getOrderByIncrementId($id);
                        $paymentCode = $order->getPayment()->getMethod();
                        $midtransResult = $this->midtransGetStatus($orderIdRequest, $paymentCode);
                    }
                } // if not multishipping order
                else {
                    $order = $this->_order->loadByIncrementId($orderIdRequest);
                    $midtransResult = $this->midtransGetStatus($order);
                }
            } else {
                $checkoutSession = $this->_checkoutSession->getData();
                $param = $this->_checkoutSession->getLastRealOrder()->getIncrementId();

                if (isset($checkoutSession['checkout_state']) && $checkoutSession['checkout_state'] === 'multishipping_success') {
                    $quoteId = $checkoutSession['last_quote_id'];
                    $incrementIds = $this->paymentOrderRepository->getIncrementIdsByQuoteId($quoteId);

                    $paymentCode = null;
                    foreach ($incrementIds as $key => $orderId) {
                        $order  = $this->paymentOrderRepository->getOrderByIncrementId($orderId);
                        $paymentCode = $order->getPayment()->getMethod();
                    }
                    $param = 'multishipping-' . $quoteId;
                    $midtransResult = $this->midtransGetStatus($param, $paymentCode);
                } elseif ($param !== null) {
                    $order = $this->paymentOrderRepository->getOrderByIncrementId($param);
                    $midtransResult = $this->midtransGetStatus($order);
                } else {
                    return $this->resultRedirectFactory->create()->setPath('checkout/cart');
                }
                $this->unSetValue();
            }

            $orderId = $midtransResult->order_id;
            $amount = $midtransResult->gross_amount;
            $transaction = $midtransResult->transaction_status;
            $payment_type = $midtransResult->payment_type;

            $this->registry->register('amount', $amount, false);
            $this->registry->register('transaction_status', $transaction, false);
            $this->registry->register('payment_type', $payment_type, false);
            $this->registry->register('order_id', $orderId, false);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->_midtransLogger->midtransError('FinishController-' . $e->getMessage());
            $this->unSetValue();
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        $resultPage = $this->_pageFactory->create();
        return $resultPage;
    }
}
