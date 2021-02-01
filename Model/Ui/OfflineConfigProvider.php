<?php

namespace Midtrans\Snap\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Midtrans\Snap\Helper\Data;

class OfflineConfigProvider implements ConfigProviderInterface
{
    const CODE = 'offline';
    protected $data;

    /**
     * SpecificConfigProvider constructor.
     * @param $data
     */
    public function __construct(Data $data)
    {
        $this->data = $data;
    }

    /**
     * Retrieve assoc array of checkout configuration
     * @return array
     */
    public function getConfig()
    {
        $production = $this->data->isProduction();
        $clientkey = $this->data->getClientKey(self::CODE);
        $merchantid = $this->data->getMerchantId(self::CODE);
        $enableredirect = $this->data->isRedirect();
        $mixpanelkey = $this->data->getMixPanelKey();

        $magentoversion = ObjectManager::getInstance()->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();

        $pluginversion = $this->data->getModuleVersion();

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
