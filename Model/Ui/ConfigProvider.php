<?php

namespace Midtrans\Snap\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Midtrans\Snap\Helper\MidtransDataConfiguration;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'snap';
    protected $midtransDataConfiguration;

    public function __construct(MidtransDataConfiguration $midtransDataConfiguration)
    {
        $this->midtransDataConfiguration = $midtransDataConfiguration;
    }

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
