<?php

namespace Midtrans\Snap\Controller\Payment;

use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context as ContextHttp;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DB\Transaction;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Sales\Model\Service\InvoiceService;
use Midtrans\Snap\Gateway\Config\Config;
use Midtrans\Snap\Gateway\Transaction as MidtransTransaction;
use Midtrans\Snap\Gateway\Utility\PaymentUtils;
use Midtrans\Snap\Helper\MidtransDataConfiguration;
use Midtrans\Snap\Logger\MidtransLogger;
use Midtrans\Snap\Model\PaymentRequestRepository;

abstract class Action implements ActionInterface
{
    /**
     * @var SessionManagerInterface
     */
    protected $_coreSession;

    /**
     * @var MidtransDataConfiguration
     */
    protected $midtransDataConfiguration;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var InvoiceService
     */
    protected $_invoiceService;

    /**
     * @var Transaction
     */
    protected $_transaction;

    /**
     * @var ResourceModel
     */
    protected $_resourceModel;

    /**
     * @var OrderRepository
     */
    protected $_orderRepository;

    /**
     * @var Order\Invoice
     */
    protected $_invoice;

    /**
     * @var Order\CreditmemoFactory
     */
    protected $_creditmemoFactory;

    /**
     * @var CreditmemoService
     */
    protected $_creditmemoService;

    /**
     * @var Order\CreditmemoRepository
     */
    protected $_creditmemoRepository;

    /**
     * @var MidtransLogger
     */
    protected $_midtransLogger;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var ContextHttp
     */
    protected $_contextHttp;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Midtrans\Snap\Model\Order\OrderRepository
     */
    protected $paymentOrderRepository;

    /**
     * @var PaymentRequestRepository
     */
    protected $paymentRequestRepository;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Action constructor.
     *
     * @param Context $context
     * @param SessionManagerInterface $coreSession
     * @param Session $checkoutSession
     * @param Order $order
     * @param ObjectManagerInterface $objectManager
     * @param MidtransDataConfiguration $midtransDataConfiguration
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     * @param ResourceModel $resourceModel
     * @param OrderRepository $orderRepository
     * @param Order\Invoice $invoice
     * @param Order\CreditmemoFactory $creditmemoFactory
     * @param CreditmemoService $creditmemoService
     * @param Order\CreditmemoRepository $creditmemoRepository
     * @param MidtransLogger $midtransLogger
     * @param Registry $registry
     * @param CustomerSession $customerSession
     * @param ContextHttp $contextHttp
     * @param PageFactory $pageFactory
     * @param \Midtrans\Snap\Model\Order\OrderRepository $paymentOrderRepository
     * @param PaymentRequestRepository $paymentRequestRepository
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $coreSession,
        Session $checkoutSession,
        Order $order,
        ObjectManagerInterface $objectManager,
        MidtransDataConfiguration $midtransDataConfiguration,
        InvoiceService $invoiceService,
        Transaction $transaction,
        ResourceModel $resourceModel,
        OrderRepository $orderRepository,
        Order\Invoice $invoice,
        Order\CreditmemoFactory $creditmemoFactory,
        CreditmemoService $creditmemoService,
        Order\CreditmemoRepository $creditmemoRepository,
        MidtransLogger $midtransLogger,
        Registry $registry,
        CustomerSession $customerSession,
        ContextHttp $contextHttp,
        PageFactory $pageFactory,
        \Midtrans\Snap\Model\Order\OrderRepository $paymentOrderRepository,
        PaymentRequestRepository $paymentRequestRepository
    ) {
        $this->_coreSession = $coreSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_order = $order;
        $this->midtransDataConfiguration = $midtransDataConfiguration;
        $this->objectManager = $objectManager;
        $this->_pageFactory = $pageFactory;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_resourceModel = $resourceModel;
        $this->_orderRepository = $orderRepository;
        $this->_invoice = $invoice;
        $this->_creditmemoFactory = $creditmemoFactory;
        $this->_creditmemoService = $creditmemoService;
        $this->_creditmemoRepository = $creditmemoRepository;
        $this->_midtransLogger = $midtransLogger;
        $this->registry = $registry;
        $this->_customerSession = $customerSession;
        $this->_contextHttp = $contextHttp;
        $this->context = $context;
        $this->resultFactory = $context->getResultFactory();
        $this->_redirect = $context->getRedirect();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->_request = $context->getRequest();
        $this->_response = $context->getResponse();
        $this->paymentOrderRepository = $paymentOrderRepository;
        $this->paymentRequestRepository = $paymentRequestRepository;
    }

    /**
     * Retrieve request object
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve response object
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Set redirect into response
     *
     * @param string $path
     * @param array $arguments
     * @return ResponseInterface
     */
    protected function _redirect($path, $arguments = [])
    {
        $this->_redirect->redirect($this->getResponse(), $path, $arguments);
        return $this->getResponse();
    }

    /**
     * Get increment id from last order checkout session
     *
     * @return string
     */
    public function getOrderIdSession()
    {
        return $this->_checkoutSession->getLastRealOrder()->getIncrementId();
    }

    /**
     * Get order by incrementId from session checkout
     *
     * @return Order
     */
    public function getOrderFromSession()
    {
        return $this->_order->loadByIncrementId($this->getOrderIdSession());
    }

    /**
     * Get Midtrans data config
     *
     * @return MidtransDataConfiguration
     */
    public function getMidtransDataConfig()
    {
        return $this->midtransDataConfiguration;
    }

    /**
     * Set value to core session
     *
     * @param $order_id
     */
    public function setValue($order_id)
    {
        $this->_coreSession->start();
        $this->_coreSession->setMessage($order_id);
    }

    /**
     * get value from core session
     *
     * @return mixed
     */
    public function getValue()
    {
        $this->_coreSession->start();
        return $this->_coreSession->getMessage();
    }

    /**
     * unset value from core sessions
     *
     * @return mixed
     */
    public function unSetValue()
    {
        $this->_coreSession->start();
        return $this->_coreSession->unsMessage();
    }

    /**
     * Get Midtrans status via API
     *
     * @param mixed $param it can be Midtrans order-id/transaction-id or Magento Order object
     * @param null $paymentCode Magento payment method code
     * @return mixed[] Midtrans API response
     * @throws \Exception
     */
    public function midtransGetStatus($param, $paymentCode = null, $transactionId = null, $paymentType = null)
    {
        $orderId = null;
        if ($param instanceof Order) {
            $orderId = $param->getIncrementId();
            $paymentCode = $param->getPayment()->getMethod();
        } else {
            $orderId = $param;
        }
        if (PaymentUtils::isOpenApi($paymentType)){
            $orderId = $transactionId;
        }
        Config::$serverKey = $this->getMidtransDataConfig()->getServerKey($paymentCode);
        Config::$isProduction = $this->getMidtransDataConfig()->isProduction();
        return MidtransTransaction::status($orderId, $paymentType);
    }
}
