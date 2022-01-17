<?php

namespace Midtrans\Snap\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Midtrans\Snap\Helper\MidtransDataConfiguration;

class Version extends Field
{
    /**
     * @var MidtransDataConfiguration
     */
    protected $midtransDataConfiguration;

    /**
     * Version constructor.
     * @param Context $context
     * @param array $data
     * @param MidtransDataConfiguration $midtransDataConfiguration
     */
    public function __construct(MidtransDataConfiguration $midtransDataConfiguration, Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->midtransDataConfiguration = $midtransDataConfiguration;
    }

    /**
     * Retrieve the setup version of the extension
     *
     * @param AbstractElement $element
     * @return string
     * @throws \Zend_Json_Exception
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $moduleVersion = $this->midtransDataConfiguration->getModuleVersion();
        $response = $this->getModuleLatestVersion();

        $latestModuleVersion = isset($response['tag_name']) ? $response['tag_name'] : 'Not Found!';
        $urlVersion = isset($response['html_url']) ? $response['html_url'] : '#';

        $html = '<td>
                        <label for="module_version">
                            <span><b>Installed Version</b></span>
                        </label>
                        <div class="value"></div>
                        ' . 'v' . $moduleVersion . '
                    </td>
                    <td>
                        <label for="current_version">
                            <span><b>Latest Version</b></span>
                        </label>
                        <div class="value"></div>
                        <a target="_blank" href="' . $urlVersion . '">' . $latestModuleVersion . '</a>
                    </td>';
        return $html;
    }

    /**
     * Function to get latest version plugins from release Github repo
     *
     * @return array|mixed|null
     * @throws \Zend_Json_Exception
     */
    protected function getModuleLatestVersion()
    {
        $curl = curl_init();
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.github.com/repos/Midtrans/Midtrans-Magento2/releases/latest',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_USERAGENT => $agent,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($httpCode === 200) {
            return \Zend_Json::decode($response);
        } else {
            $response = [];
            return $response;
        }
    }
}
