<?php

namespace Midtrans\Snap\Helper;

use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Midtrans\Snap\Model\Config\Source\Payment\Settings;

class MidtransDataConfiguration
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var EncryptorInterface
     */
    private $_encryptor;

    /**
     * Serialize data to JSON, unserialize JSON encoded data
     * @var Json
     */
    public $json;

    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * Data constructor.
     * @param Settings $settings
     * @param EncryptorInterface $encryptor
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param Json $json
     */
    public function __construct(
        Settings $settings,
        EncryptorInterface $encryptor,
        ComponentRegistrarInterface $componentRegistrar,
        Json $json
    ) {
        $this->settings = $settings;
        $this->_encryptor = $encryptor;
        $this->componentRegistrar = $componentRegistrar;
        $this->json = $json;
    }

    public function getMixPanelKey()
    {
        return $this->isProduction() == true ? '17253088ed3a39b1e2bd2cbcfeca939a' : '9dcba9b440c831d517e8ff1beff40bd9';
    }

    public function getNotificationEndpoint()
    {
        return $this->settings->getNotificationEndpoint();
    }

    public function isOverrideNotification()
    {
        return $this->settings->isOverrideNotification();
    }

    public function enableLog()
    {
        return $this->settings->enableLog();
    }

    public function getMerchantId($code)
    {
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

    public function isRedirect()
    {
        return $this->settings->isRedirect();
    }

    public function isProduction()
    {
        return $this->settings->isProduction();
    }

    public function getServerKey($paymentCode)
    {
        if ($paymentCode == 'snap') {
            $serverKey = $this->settings->getDefaultServerKey();
            return $this->_encryptor->decrypt($serverKey);
        } elseif ($paymentCode == 'specific') {
            $serverKey = $this->settings->getSpecificServerKey();
            return $this->_encryptor->decrypt($serverKey);
        } elseif ($paymentCode == 'installment') {
            $serverKey = $this->settings->getInstallmentServerKey();
            return $this->_encryptor->decrypt($serverKey);
        } elseif ($paymentCode == 'offline') {
            $serverKey = $this->settings->getOfflineServerKey();
            return $this->_encryptor->decrypt($serverKey);
        }
    }

    public function getClientKey($paymentCode)
    {
        if ($paymentCode == 'snap') {
            $clientKey = $this->settings->getDefaultClientKey();
            return $this->_encryptor->decrypt($clientKey);
        } elseif ($paymentCode == 'specific') {
            $clientKey = $this->settings->getSpecificClientKey();
            return $this->_encryptor->decrypt($clientKey);
        } elseif ($paymentCode == 'installment') {
            $clientKey = $this->settings->getInstallmentClientKey();
            return $this->_encryptor->decrypt($clientKey);
        } elseif ($paymentCode == 'offline') {
            $clientKey = $this->settings->getOfflineClientKey();
            return $this->_encryptor->decrypt($clientKey);
        }
    }

    public function getRequestConfig($paymentCode)
    {
        if ($paymentCode == 'snap') {
            return $this->settings->getConfigSnap();
        } elseif ($paymentCode == 'specific') {
            return $this->settings->getConfigSpecific();
        } elseif ($paymentCode == 'installment') {
            return $this->settings->getConfigInstallment();
        } elseif ($paymentCode == 'offline') {
            return $this->settings->getConfigOffline();
        }
    }

    public function getModuleVersion()
    {
        $moduleDir = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            'Midtrans_Snap'
        );

        $composerJson = file_get_contents($moduleDir . '/composer.json');
        $composerJson = $this->json->unserialize($composerJson);

        if (empty($composerJson['version'])) {
            return "Version is not available in composer.json";
        }

        return $composerJson['version'];
    }
}
