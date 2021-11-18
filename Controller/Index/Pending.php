<?php

namespace Midtrans\Snap\Controller\Index;

use Magento\Checkout\Model\Session\SuccessValidator;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Pending extends Action
{
    protected $request;
    protected $resultPageFactory;

    public function __construct(Context $context, PageFactory $pageFactory)
    {
        $this->resultPageFactory = $pageFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->_objectManager->get(SuccessValidator::class)->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        return $this->resultPageFactory->create();
    }
}
