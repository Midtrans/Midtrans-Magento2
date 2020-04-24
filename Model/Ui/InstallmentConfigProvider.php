<?php

namespace Midtrans\Snap\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Midtrans\Snap\Helper\Data;

class InstallmentConfigProvider implements ConfigProviderInterface
{
    const CODE = 'installment';
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

        $composer = file_get_contents(dirname(__FILE__) . '/../../composer.json');
        $json = json_decode($composer, true); // decode the JSON into an associative array
        $pluginversion = $json['version'];

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
