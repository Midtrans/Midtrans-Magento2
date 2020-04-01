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
//    protected $config;
//    protected $request;
//    protected $assetRepo;
//    protected $logger;
//    protected $urlBuilder;
//    protected $paymentHelper;
//    protected $methods = [];
//    protected $_checkoutSession;
    protected $data;


    public function __construct(
//        PaymentConfig $paymentConfig,
//        Repository $assetRepo,
//        RequestInterface $request,
//        UrlInterface $urlBuilder,
        Data $data
//        Session $checkoutSession,
//        LoggerInterface $logger
    )
    {

//        $this->urlBuilder = $urlBuilder;
//        $this->logger = $logger;
        $this->data = $data;
//        $this->config = $paymentConfig;
//        $this->assetRepo = $assetRepo;
//        $this->_checkoutSession = $checkoutSession;
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
