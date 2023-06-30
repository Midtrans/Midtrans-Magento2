<?php

namespace Midtrans\Snap\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\HTTP\Client\Curl;
use Midtrans\Snap\Helper\MidtransDataConfiguration;

class Version extends Field
{
    /**
     * @var MidtransDataConfiguration
     */
    protected $midtransDataConfiguration;

    /**
     * @var Curl Magento CURL
     */
    protected $magentoCurl;

    /**
     * Version constructor.
     * @param Context $context
     * @param array $data
     * @param MidtransDataConfiguration $midtransDataConfiguration
     */
    public function __construct(MidtransDataConfiguration $midtransDataConfiguration, Context $context, Curl $curl, array $data = [])
    {
        parent::__construct($context, $data);
        $this->midtransDataConfiguration = $midtransDataConfiguration;
        $this->magentoCurl = $curl;
    }

    /**
     * Retrieve the setup version of the extension
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $moduleVersion = $this->midtransDataConfiguration->getModuleVersion();
        return '<td>
                        <label for="module_version">
                            <span><b>Installed Version</b></span>
                        </label>
                        <div class="value"></div>
                        ' . 'v' . $moduleVersion . '
                    </td>
                    <td>
                        <label for="current_version">
                            <span><b>Midtrans Magento Latest Version</b></span>
                        </label>
                        <div class="value"></div>
                        <a target="_blank" href="https://github.com/Midtrans/Midtrans-Magento2/releases/latest">Check Latest version</a>
                    </td>';
    }

    /**
     * Function to get latest version plugins from release Github repo
     *
     * @return array|mixed|null
     * @deprecated In order to reduce reliance on external APIs, we have made the decision to remove this function in our upcoming major release.
     */
    protected function getModuleLatestVersion()
    {
        $this->magentoCurl->addHeader("Content-Type", "application/json");
        $this->magentoCurl->addHeader("Content-Length", 200);
        $this->magentoCurl->addHeader('User-Agent', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');
        $this->magentoCurl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->magentoCurl->get('https://api.github.com/repos/Midtrans/Midtrans-Magento2/releases/latest');

        if ($this->magentoCurl->getStatus() === 200) {
            return $this->magentoCurl->getBody();
        } else {
            return [];
        }
    }
}
