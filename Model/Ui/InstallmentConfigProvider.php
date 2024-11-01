<?php

namespace Midtrans\Snap\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Midtrans\Snap\Helper\MidtransDataConfiguration;

class InstallmentConfigProvider implements ConfigProviderInterface
{
    const CODE = 'installment';
    protected $midtransDataConfiguration;

    /**
     * SpecificConfigProvider constructor.
     * @param $midtransDataConfiguration
     */
    public function __construct(MidtransDataConfiguration $midtransDataConfiguration)
    {
        $this->midtransDataConfiguration = $midtransDataConfiguration;
    }

    /**
     * Retrieve assoc array of checkout configuration
     * @return array
     */
    public function getConfig()
    {
        $production = $this->midtransDataConfiguration->isProduction();
        $clientkey = $this->midtransDataConfiguration->getClientKey(self::CODE);
        $merchantid = $this->midtransDataConfiguration->getMerchantId(self::CODE);
        $enableredirect = $this->midtransDataConfiguration->isRedirect();
        $mixpanelkey = $this->midtransDataConfiguration->getMixPanelKey();

        $magentoversion = ObjectManager::getInstance()->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();

        $pluginversion = $this->midtransDataConfiguration->getModuleVersion();

        return [
            'payment' => [
                self::CODE => [
                    'production' => $production,
                    'clientkey' => $clientkey,
                    'merchantid' => $merchantid,
                    'enableredirect' => $enableredirect,
                    'mixpanelkey' => $mixpanelkey,
                    'magentoversion' => $magentoversion,
                    'pluginversion' => $pluginversion
                ]
            ]
        ];
    }
}
