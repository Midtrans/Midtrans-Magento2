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
