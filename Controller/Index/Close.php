<?php

namespace Midtrans\Snap\Controller\Index;

use Magento\Checkout\Model\Session\SuccessValidator;
use Midtrans\Snap\Controller\Payment\AbstractAction;

class Close extends AbstractAction
{
    public function execute()
    {
        if (!$this->_objectManager->get(SuccessValidator::class)->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        $resultPage = $this->_pageFactory->create();
        return $resultPage;
    }
}
