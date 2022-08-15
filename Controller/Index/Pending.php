<?php

namespace Midtrans\Snap\Controller\Index;

use Magento\Checkout\Model\Session\SuccessValidator;
use Midtrans\Snap\Controller\Payment\Action;

/**
 * Class Pending controller is to handle information when the payment order is still pending
 * @deprecated since version 2.5.5 Pending class no longer used, the pending page merged on finish page. Will be deleted
 * on the next major release
 * @see \Midtrans\Snap\Controller\Index\Finish
 *
 */
class Pending extends Action
{
    public function execute()
    {
        $param = $this->getValue();

        if (!$this->objectManager->get(SuccessValidator::class)->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        return $this->_pageFactory->create();
    }
}
