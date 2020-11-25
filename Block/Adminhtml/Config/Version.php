<?php

namespace Midtrans\Snap\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Midtrans\Snap\Helper\Data;

class Version extends Field
{
    protected $_midtransHelper;

    /**
     * Version constructor.
     * @param Context $context
     * @param array $data
     * @param Data $midtransHelper
     */
    public function __construct(Data $midtransHelper, Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_midtransHelper = $midtransHelper;
    }

    /**
     * Retrieve the setup version of the extension
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_midtransHelper->getModuleVersion();
    }
}
