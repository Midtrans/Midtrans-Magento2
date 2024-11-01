<?php

namespace Midtrans\Snap\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Midtrans\Snap\Helper\MidtransDataConfiguration;

class NotificationEndpoint extends Field
{
    /**
     * @var MidtransDataConfiguration
     */
    protected $midtransDataConfiguration;

    /**
     * constructor.
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
     * Retrieve the midtrans Notification endpoint
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->midtransDataConfiguration->getNotificationEndpoint();
    }
}
