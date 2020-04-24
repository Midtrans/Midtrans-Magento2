<?php

namespace Midtrans\Snap\Controller\Payment;

use Exception;
use Magento\Framework\Controller\ResultFactory;
use Midtrans\Snap\Gateway\SnapApi;
use Midtrans\Snap\Gateway\Config\Config;

class Redirect extends AbstractAction
{
    public function execute()
    {
        $paymentCode = $this->getCode();
        $requestConfig = $this->getData()->getRequestConfig($paymentCode);
        $enableRedirect = $this->getData()->isRedirect();

        $payloads = $this->getPayload($requestConfig);
        $is3ds = $requestConfig['is3ds'];

        Config::$is3ds = $is3ds;
        Config::$isProduction = $this->getData()->isProduction();
        Config::$serverKey = $this->getData()->getServerKey($paymentCode);
        Config::$isSanitized = false;

        try {
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
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $result->setData($data);
            return $result;
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->_midtransLogger->midtransError($e->getMessage());
        }
    }
}
