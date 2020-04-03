<?php
namespace Midtrans\Snap\Controller\Index;
use Magento\Checkout\Model\Session\SuccessValidator;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Sales\Model\Order;
use Midtrans\Snap\Controller\Payment\AbstractAction;

class Close extends AbstractAction implements HttpGetActionInterface
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
