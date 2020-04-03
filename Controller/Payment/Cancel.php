<?php

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
