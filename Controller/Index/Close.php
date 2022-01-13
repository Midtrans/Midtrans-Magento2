<?php

namespace Midtrans\Snap\Controller\Index;

use Magento\Sales\Model\Order;
use Midtrans\Snap\Controller\Payment\Action;

class Close extends Action
{
    public function execute()
    {
        $param = $this->getValue();

        $ordersCanceled = [];
        if ($param !== null) {
            if (strpos($param, 'multishipping-') !== false) {
                $quoteId = str_replace('multishipping-', '', $param);
                $incrementIds = $this->paymentOrderRepository->getIncrementIdsByQuoteId($quoteId);

                foreach ($incrementIds as $key => $orderId) {
                    $order  = $this->paymentOrderRepository->getOrderByIncrementId($orderId);
                    $this->closedOrder($order);
                    $ordersCanceled[$orderId] = $orderId;
                }
            } else {
                $order = $this->_order->loadByIncrementId($param);
                $this->closedOrder($order);
                $ordersCanceled[$param] = $param;
            }
            $this->registry->register('orders_canceled', $ordersCanceled, false);
        } else {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        $this->unSetValue();
        return $this->_pageFactory->create();
    }

    /**
     * Function to close order
     *
     * @param Order $order
     * @throws \Exception
     */
    private function closedOrder(Order $order)
    {
        if ($order->getState() == Order::STATE_NEW && !$order->hasInvoices()) {
            $order_note = "Midtrans | Payment Page close - by User";
            try {
                $this->paymentOrderRepository->cancelOrder($order, Order::STATE_CANCELED, $order_note);
            } catch (\Exception $e) {
                $this->_midtransLogger->midtransError('PaymentClose: ' . $e);
            }
        }
    }
}
