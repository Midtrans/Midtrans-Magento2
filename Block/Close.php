<?php

namespace Midtrans\Snap\Block;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Close extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Close block constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * Function to get orders canceled
     *
     * @return mixed|null
     */
    public function getOrdersCanceled()
    {
        $data = $this->registry->registry('orders_canceled');
        return $data;
    }
}
