<?php

namespace Midtrans\Snap\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Midtrans\Snap\Helper\Data;
use Midtrans\Snap\Model\Config\Source\Field\ModuleVersion;
use \Midtrans\Snap\Model\Snap;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Asset\Repository;
use Psr\Log\LoggerInterface;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Quote\Model\Quote;
use Magento\Checkout\Model\Session;

final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'snap';
    protected $data;


    public function __construct(
        Data $data
    )
    {
        $this->data = $data;
    }

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
