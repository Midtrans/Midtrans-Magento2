<?php
/**
 * Created by Zaki Ibrahim 2019. Copyright Midtrans PT
 */

namespace Midtrans\Snap\Controller\Index;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;

use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\OrderFactory;

$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
$filesystem = $object_manager->get('Magento\Framework\Filesystem');
$root = $filesystem->getDirectoryRead(DirectoryList::ROOT);
$lib_file = $root->getAbsolutePath('lib/internal/midtrans-php/Midtrans.php');
require_once($lib_file);


class Finish extends Action
{
    protected $registry;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $_resultPageFactory;

    protected $_checkoutSession;

    protected $_orderFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        PageFactory $pageFactory,
        Session $checkoutSession,
        OrderFactory $orderFactory
    )
    {
        $this->_resultPageFactory = $pageFactory;
        parent::__construct($context);
        $this->registry = $registry;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
    }


    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!$this->_objectManager->get(\Magento\Checkout\Model\Session\SuccessValidator::class)->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        $om = $this->_objectManager;

        try {
            $vtConfig = $om->get('Midtrans\Config');
            $config = $om->get('Magento\Framework\App\Config\ScopeConfigInterface');

            $isProduction = $config->getValue('payment/snap/is_production', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '1' ? true : false;
            $vtConfig::$isProduction = $isProduction;
            $serverKey = $config->getValue('payment/snap/server_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $vtConfig::$serverKey = $serverKey;
            $status = $om->get('Midtrans\Transaction');

            $order = $this->_checkoutSession->getLastRealOrder();
            $incrementId = $order->getIncrementId();

            $transactionId = $this->getRequest()->getParam('id');
            $orderId = $this->getRequest()->getParam('order_id');

            if ($transactionId != null) {
                $param = $transactionId;
            } else if ($orderId != null) {
                $param = $orderId;
            } else {
                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }
            $statusResult = $status->status($param);

            $orderId = $statusResult->order_id;
            $amount = $statusResult->gross_amount;
            $transaction = $statusResult->transaction_status;
            $payment_type = $statusResult->payment_type;

            $this->registry->register('amount', $amount, false);
            $this->registry->register('transaction_status', $transaction, false);
            $this->registry->register('payment_type', $payment_type, false);
            $this->registry->register('order_id', $orderId, false);

        } catch (Exception $e) {
            error_log($e->getMessage());
            echo $e->getMessage();
        }

        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }

}