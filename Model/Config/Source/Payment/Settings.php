<?php

namespace Midtrans\Snap\Model\Config\Source\Payment;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Settings extends AbstractPayment
{
    const SETTINGS_PAYMENT_CODE = 'settings';
    /**
     * @var EncryptorInterface
     */

    public $code = self::SETTINGS_PAYMENT_CODE ;

    protected $_scopeConfig;


    /**
     * Settings constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct
    (
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }

    public $formBlockType = 'Midtrans\Snap\Block\Form\Snap';

    protected function getDataConfig($pathXML) {
        return $this->_scopeConfig->getValue($pathXML, ScopeInterface::SCOPE_STORE);
    }


    public function isProduction()
    {
        return $this->getDataConfig('payment/snap/settings/is_production') == '1' ? true : false;
    }

    public function isRedirect() {
        return $this->getDataConfig('payment/snap/settings/enable_redirect') == '1' ? true : false;
    }

    public function getMerchantId()
    {
        return $this->getDataConfig('payment/snap/settings/merchant_id');
    }


    public function getDefaultClientKey()
    {
        if ($this->isProduction()) {
            return $this->getDataConfig('payment/snap/settings/production_client_key');
        } else {
            return $this->getDataConfig('payment/snap/settings/sandbox_client_key');
        }
    }

    public function getDefaultServerKey()
    {
        if ($this->isProduction()) {
            return $this->getDataConfig('payment/snap/settings/production_server_key');
        } else {
            return $this->getDataConfig('payment/snap/settings/sandbox_server_key');
        }
    }

    public function enableLog() {
        return $this->getDataConfig('payment/settings/enable_log') == 1 ? true : false;
    }

    public function getOrderStatus() {
        return $this->getDataConfig('payment/settings/order_status');
    }

    public function getConfigSnap() {
        $config = array();
        $config['bank'] = $this->getDataConfig('payment/snap/basic/cc_config/bank');
        $config['custom_expiry'] = $this->getDataConfig('payment/snap/custom_expiry');
        $config['is3ds'] = $this->getDataConfig('payment/snap/is3ds') == 1 ? true : false;
        $config['one_click'] = $this->getDataConfig('payment/snap/one_click') == 1 ? true : false;
        $config['bin'] = $this->getDataConfig('payment/snap/basic/cc_config/bin');
        return $config;
    }

    public function getConfigSpecific() {
        $configSpecific = array();
        $configSpecific['bank'] = $this->getDataConfig('payment/snap/specific/cc_config/bank');
        $configSpecific['custom_expiry'] = $this->getDataConfig('payment/specific/custom_expiry');
        $configSpecific['is3ds'] = $this->getDataConfig('payment/specific/is3ds') == 1 ? true : false;
        $configSpecific['one_click'] = $this->getDataConfig('payment/specific/one_click') == 1 ? true : false;
        $configSpecific['bin'] = $this->getDataConfig('payment/snap/specific/cc_config/bin');
        $configSpecific['enabled_payments'] = $this->getDataConfig('payment/snap/specific/enable_payment');
        return $configSpecific;
    }

    public function getConfigInstallment() {
        $configInstallment = array();
        $configInstallment['custom_expiry'] = $this->getDataConfig('payment/installment/custom_expiry');
        $configInstallment['is3ds'] = $this->getDataConfig('payment/installment/is3ds') == 1 ? true : false;
        $configInstallment['one_click'] = $this->getDataConfig('payment/installment/one_click') == 1 ? true : false;
        $configInstallment['minimal_amount'] = $this->getDataConfig('payment/installment/minimal_amount');
        return $configInstallment;
    }

    public function getConfigOffline() {
        $configOffline = array();
        $configOffline['bank'] = $this->getDataConfig('payment/snap/offline/cc_config/bank');
        $configOffline['custom_expiry'] = $this->getDataConfig('payment/offline/custom_expiry');
        $configOffline['is3ds'] = $this->getDataConfig('payment/offline/is3ds') == 1 ? true : false;
        $configOffline['one_click'] = $this->getDataConfig('payment/offline/one_click') == 1 ? true : false;
        $configOffline['bin'] = $this->getDataConfig('payment/snap/offline/cc_config/bin');
        $configOffline['minimal_amount'] = $this->getDataConfig('payment/offline/minimal_amount');
        $configOffline['terms'] = $this->getDataConfig('payment/snap/offline/cc_config/term');
        return $configOffline;
    }

    public function isNotificationLogEnabled()
    {
        return $this->getDataConfig('payment/settings/notification_log') == 1 ? true : false;
    }

    public function isRequestLogEnabled()
    {
        return $this->getDataConfig('payment/settings/request_log') == 1 ? true : false;
    }

    public function isErrorLogEnabled()
    {
        return $this->getDataConfig('payment/settings/error_log') == 1 ? true : false;
    }

    public function isExceptionEnabled()
    {
        return $this->getDataConfig('payment/settings/throw_exception') == 1 ? true : false;
    }

//    public function isUsedSpecificMidtransAccount()
//    {
//        return $this->getDataConfig('payment/specific/use_specific_account') == 1 ? true : false;
//    }

    public function getSpecificMerchantId()
    {
        return $this->getDataConfig('payment/snap/specific/specific_access/merchant_id');
    }

    public function getSpecificClientKey()
    {
        if ($this->isProduction()) {
            if ($this->getDataConfig('payment/specific/use_specific_account') == 1 ? true : false) {
                return $this->getDataConfig('payment/snap/specific/specific_access/production_client_key');
            } else {
                return $this->getDefaultServerKey();
            }
        } else {
            if ($this->getDataConfig('payment/specific/use_specific_account') == 1 ? true : false) {
                return $this->getDataConfig('payment/snap/specific/specific_access/sandbox_client_key');
            } else {
                return $this->getDefaultServerKey();
            }
        }
    }

    public function getSpecificServerKey()
    {
        if ($this->isProduction()) {
            if ($this->getDataConfig('payment/specific/use_specific_account') == 1 ? true : false) {
                return $this->getDataConfig('payment/snap/specific/specific_access/production_server_key');
            } else {
                return $this->getDefaultServerKey();
            }
        } else {
            if ($this->getDataConfig('payment/specific/use_specific_account') == 1 ? true : false) {
                return $this->getDataConfig('payment/snap/specific/specific_access/sandbox_server_key');
            } else {
                return $this->getDefaultServerKey();
            }
        }
    }

    public function getInstallmentMerchantId()
    {
        return $this->getDataConfig('payment/snap/installment/installment_access/merchant_id');
    }

    public function getInstallmentClientKey()
    {
        if ($this->isProduction()) {
            if ($this->getDataConfig('payment/installment/use_specific_account') == 1 ? true : false) {
                return $this->getDataConfig('payment/snap/installment/installment_access/production_client_key');
            } else {
                return $this->getDefaultServerKey();
            }
        } else {
            if ($this->getDataConfig('payment/installment/use_specific_account') == 1 ? true : false) {
                return $this->getDataConfig('payment/snap/installment/installment_access/sandbox_client_key');
            } else {
                return $this->getDefaultServerKey();
            }
        }
    }

    public function getInstallmentServerKey()
    {
        if ($this->isProduction()) {
            if ($this->getDataConfig('payment/installment/use_specific_account') == 1 ? true : false) {
                return $this->getDataConfig('payment/snap/installment/installment_access/production_server_key');
            } else {
                return $this->getDefaultServerKey();
            }
        } else {
            if ($this->getDataConfig('payment/offline/use_specific_account') == 1 ? true : false) {
                return $this->getDataConfig('payment/snap/installment/installment_access/sandbox_server_key');
            } else {
                return $this->getDefaultServerKey();
            }
        }
    }

    public function getOfflineMerchantId()
    {
        return $this->getDataConfig('payment/snap/offline/offline_access/merchant_id');
    }

    public function getOfflineClientKey()
    {
        if ($this->isProduction()) {
            if ($this->getDataConfig('payment/offline/use_specific_account') == 1 ? true : false) {
                return $this->getDataConfig('payment/snap/offline/offline_access/production_client_key');
            } else {
                return $this->getDefaultServerKey();
            }
        } else {
            if ($this->getDataConfig('payment/offline/use_specific_account') == 1 ? true : false) {
                return $this->getDataConfig('payment/snap/offline/offline_access/sandbox_client_key');
            } else {
                return $this->getDefaultServerKey();
            }
        }
    }

    public function getOfflineServerKey()
    {
        if ($this->isProduction()) {
            if ($this->getDataConfig('payment/offline/use_specific_account') == 1 ? true : false) {
                return $this->getDataConfig('payment/snap/offline/offline_access/production_server_key');
            } else {
                return $this->getDefaultServerKey();
            }
        } else {
            if ($this->getDataConfig('payment/offline/use_specific_account') == 1 ? true : false) {
                return $this->getDataConfig('payment/snap/offline/offline_access/sandbox_server_key');
            } else {
                return $this->getDefaultServerKey();
            }
        }
    }


}
