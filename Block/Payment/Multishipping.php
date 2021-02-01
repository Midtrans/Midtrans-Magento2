<?php

namespace Midtrans\Snap\Block\Payment;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Midtrans\Snap\Model\Ui\ConfigProvider;
use Midtrans\Snap\Model\Ui\InstallmentConfigProvider;
use Midtrans\Snap\Model\Ui\OfflineConfigProvider;
use Midtrans\Snap\Model\Ui\SpecificConfigProvider;

class Multishipping extends Template
{
    protected $registry;

    /** @var PriceCurrencyInterface $priceCurrency */
    protected $priceCurrency;

    protected $configProvider;
    protected $installmentConfigProvider;
    protected $offlineConfigProvider;
    protected $specificConfigProvider;

    /**
     * Multishipping constructor.
     *
     * @param Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param Registry $registry
     * @param ConfigProvider $configProvider
     * @param InstallmentConfigProvider $installmentConfigProvider
     * @param OfflineConfigProvider $offlineConfigProvider
     * @param SpecificConfigProvider $specificConfigProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        PriceCurrencyInterface $priceCurrency,
        Registry $registry,
        ConfigProvider $configProvider,
        InstallmentConfigProvider $installmentConfigProvider,
        OfflineConfigProvider $offlineConfigProvider,
        SpecificConfigProvider $specificConfigProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->priceCurrency = $priceCurrency;
        $this->configProvider = $configProvider;
        $this->installmentConfigProvider = $installmentConfigProvider;
        $this->offlineConfigProvider = $offlineConfigProvider;
        $this->specificConfigProvider = $specificConfigProvider;
    }

    /**
     * Function to get billing address
     *
     * @return mixed
     */
    public function getBillingAddress()
    {
        $data['full_name'] = $this->registry->registry('full_name');
        $data['address'] = $this->registry->registry('address');
        $data['postal_code'] = $this->registry->registry('postal_code');
        $data['country_code'] = $this->registry->registry('country_code');
        $data['email'] = $this->registry->registry('email');
        $data['phone'] = $this->registry->registry('phone');
        return $data;
    }

    /**
     * Function to get Snap token
     *
     * @return mixed|null
     */
    public function getSnapToken()
    {
        return $this->registry->registry('token');
    }

    /**
     * Function get order gross amount
     *
     * @return string
     */
    public function getGrossAmount()
    {
        $amount = $this->registry->registry('amount');
        return $this->priceCurrency->convertAndFormat($amount);
    }

    /**
     * Function to get Payment Configuration
     *
     * @return array|mixed
     */
    public function getPaymentConfig()
    {
        $paymentCode = $this->registry->registry('payment_code');
        $configProvider = null;
        if ($paymentCode === 'specific') {
            $configProvider = $this->specificConfigProvider->getConfig();
        } elseif ($paymentCode === 'installment') {
            $configProvider = $this->installmentConfigProvider->getConfig();
        } elseif ($paymentCode === 'offline') {
            $configProvider = $this->offlineConfigProvider->getConfig();
        } else {
            $configProvider = $this->configProvider->getConfig();
        }
        $config = $configProvider['payment'][$paymentCode];
        $config['token'] = $this->registry->registry('token');
        if ($config['production']) {
            $config['js'] = 'https://app.midtrans.com/snap/snap.js';
        } else {
            $config['js'] = 'https://app.sandbox.midtrans.com/snap/snap.js';
        }

        return $config;
    }

    /**
     * Get payment close page URL
     *
     * @return string|null
     */
    public function getCloseUrl()
    {
        try {
            return $this->_storeManager->getStore()->getBaseUrl() . 'snap/index/close';
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
