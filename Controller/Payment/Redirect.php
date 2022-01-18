<?php

namespace Midtrans\Snap\Controller\Payment;

use Exception;
use Magento\Framework\Controller\ResultFactory;
use Midtrans\Snap\Gateway\Config\Config;
use Midtrans\Snap\Gateway\SnapApi;

class Redirect extends Action
{
    public function execute()
    {
        try {
            $incrementId = $this->_checkoutSession->getLastRealOrder()->getIncrementId();
            $paymentCode = $this->paymentOrderRepository->getPaymentCode($incrementId);

            $requestConfig = $this->getMidtransDataConfig()->getRequestConfig($paymentCode);
            $enableRedirect = $this->getMidtransDataConfig()->isRedirect();

            $order = $this->getOrderFromSession();

            $payloads = $this->paymentRequestRepository->getPayload($requestConfig, $paymentCode, $order);
            $is3ds = $requestConfig['is3ds'];
            $isProduction = $this->getMidtransDataConfig()->isProduction();

            Config::$isProduction = $isProduction;
            Config::$serverKey = $this->getMidtransDataConfig()->getServerKey($paymentCode);
            Config::$isSanitized = true;
            Config::$is3ds = $is3ds;

            /*Override notification, if override notification from admin setting is active (default is active) */
            if ($this->getMidtransDataConfig()->isOverrideNotification() && $this->getMidtransDataConfig()->getNotificationEndpoint() != null) {
                Config::$overrideNotifUrl = $this->getMidtransDataConfig()->getNotificationEndpoint();
            }

            $_info = 'Info - Payloads: ' . print_r($payloads, true);
            $this->_midtransLogger->midtransRequest($_info);

            $snapApi = new SnapApi();
            $data = null;
            if (!$enableRedirect) {
                $token = $snapApi::getSnapToken($payloads);
                $data = $token;
                $_info = 'Info - Snap token: ' . print_r($data, true);
                $this->_midtransLogger->midtransRequest($_info);
            } else {
                $redirect_url = $snapApi::createTransaction($payloads)->redirect_url;
                $data = $redirect_url;
                $_info = 'Info - Redirect URL :' . print_r($data, true);
                $this->_midtransLogger->midtransRequest($_info);
            }
            $paymentOrderId = $payloads['transaction_details']['order_id'];
            $this->setValue($paymentOrderId);
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $result->setData($data);
            return $result;
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->_midtransLogger->midtransError($e->getMessage());
        }
    }
}
