<?php

namespace Midtrans\Snap\Block\Adminhtml\Order\View\Tab;

class Info extends \Magento\Sales\Block\Adminhtml\Order\View\Tab\Info
{
    /**
     * Get Payment method information
     *
     * @return string
     */
    public function getMidtransPaymentInfo()
    {
        $result = '';
        $payment = $this->getOrder()->getPayment();
        $paymentCode = $payment->getMethod();
        if ($paymentCode === 'snap' || $paymentCode === 'specific' || $paymentCode === 'installment' || $paymentCode === 'offline') {
            $paymentInfo = $payment->getAdditionalInformation();
            $result = $this->getTableInfoTemplate($paymentInfo);
            return $result;
        } else {
            return $result;
        }
    }

    /**
     * Setup template to show payment info
     *
     * @param array $param
     * @return string
     */
    private function getTableInfoTemplate(array $param)
    {
        $paymentGateway = isset($param['payment_gateway']) ? $param['payment_gateway'] : "-";
        $merchantId = isset($param['merchant_id']) ? $param['merchant_id'] : "-";
        $orderId = isset($param['midtrans_order_id']) ? $param['midtrans_order_id'] : "-";
        $paymentMethod = isset($param['payment_method']) ? $param['payment_method'] : "-";
        $transactionId = isset($param['transaction_id']) ? $param['transaction_id'] : "-";

        return '<table class="admin__table-secondary order-payment-information-table">
                    <tr>
                        <th><b>Payment Gateway</b></th>
                        <td><span>: ' . $paymentGateway . '</span></td>
                    </tr>
                    <tr>
                        <th><b>Merchant ID</b></th>
                        <td><span>: ' . $merchantId . '</span></td>
                    </tr>
                    <tr>
                        <th><b>Midtrans Order-ID</b></th>
                        <td><span>: ' . $orderId . '</span></td>
                    </tr>
                    <tr>
                        <th><b>Payment Method</b></th>
                        <td>: ' . $paymentMethod . '</td>
                    </tr>
                </table>';
    }
}
