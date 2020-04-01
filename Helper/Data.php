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

namespace Midtrans\Snap\Helper;

use Magento\Framework\Encryption\EncryptorInterface;
use Midtrans\Snap\Model\Config\Source\Payment\Settings;
use Midtrans\Snap\Model\Config\Source\Payment\Specific;

class Data
{
    private $settings;
    private $_encryptor;

    public function __construct(
        Settings $settings,
        EncryptorInterface $encryptor
    )
    {
        $this->settings = $settings;
        $this->_encryptor = $encryptor;
    }


    public function getMixPanelKey() {
        return $this->isProduction() == true ? '17253088ed3a39b1e2bd2cbcfeca939a' : '9dcba9b440c831d517e8ff1beff40bd9';
    }

    public function enableLog() {
        $enableLog = $this->settings->enableLog();
        return $enableLog;
    }

    public function getMerchantId($code) {
        if ($code == 'snap') {
            return $this->settings->getMerchantId();
        } elseif ($code == 'specific') {
            return $this->settings->getSpecificMerchantId();
        } elseif ($code == 'installment') {
            return $this->settings->getInstallmentMerchantId();
        } elseif ($code == 'offline') {
            return $this->settings->getOfflineMerchantId();
        }
    }

    public function isRedirect() {
        $isRedirect = $this->settings->isRedirect();
        return $isRedirect;
    }

    public function isProduction() {
        $isProduction = $this->settings->isProduction();
        return $isProduction;
    }

    public function getServerKey($paymentCode) {
        if ($paymentCode == 'snap') {
            $serverKey = $this->settings->getDefaultServerKey();
            $key = $this->_encryptor->decrypt($serverKey);
            return $key;
        } elseif ($paymentCode == 'specific') {
            $serverKey = $this->settings->getSpecificServerKey();
            $specificServerKey = $this->_encryptor->decrypt($serverKey);
            return $specificServerKey;
        } elseif ($paymentCode == 'installment') {
            $serverKey = $this->settings->getInstallmentServerKey();
            $installmentServerKey = $this->_encryptor->decrypt($serverKey);
            return $installmentServerKey;
        } elseif ($paymentCode == 'offline') {
            $serverKey = $this->settings->getOfflineServerKey();
            $offlineServerKey = $this->_encryptor->decrypt($serverKey);
            return $offlineServerKey;
        }
    }

    public function getClientKey($paymentCode) {
        if ($paymentCode == 'snap') {
            $clientKey = $this->settings->getDefaultClientKey();
            $key = $this->_encryptor->decrypt($clientKey);
            return $key;
        } elseif ($paymentCode == 'specific') {
            $clientKey = $this->settings->getSpecificClientKey();
            $specificClientKey = $this->_encryptor->decrypt($clientKey);
            return $specificClientKey;
        } elseif ($paymentCode == 'installment') {
            $clientKey = $this->settings->getInstallmentClientKey();
            $installmentClientKey = $this->_encryptor->decrypt($clientKey);
            return $installmentClientKey;
        } elseif ($paymentCode == 'offline') {
            $clientKey = $this->settings->getOfflineClientKey();
            $offlineClientKey = $this->_encryptor->decrypt($clientKey);
            return $offlineClientKey;
        }
    }

    public function getRequestConfig($paymentCode) {
        if ($paymentCode == 'snap') {
            return $this->settings->getConfigSnap();
        } else if ($paymentCode == 'specific') {
            return $this->settings->getConfigSpecific();
        } else if ($paymentCode == 'installment') {
            return $this->settings->getConfigInstallment();
        } else if ($paymentCode == 'offline') {
            return $this->settings->getConfigOffline();
        }
    }
}
