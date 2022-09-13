<?php

namespace Midtrans\Snap\Model\Order;

use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\CreditmemoRepository;
use Magento\Sales\Model\OrderRepository as MagentoOrderRepository;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Sales\Model\Service\InvoiceService;
use Midtrans\Snap\Logger\MidtransLogger;

class OrderRepository
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var MagentoOrderRepository
     */
    protected $magentoOrderRepository;

    /**
     * @var MidtransLogger
     */
    protected $midtransLogger;

    /**
     * @var Order\Invoice
     */
    protected $invoice;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @var CreditmemoService
     */
    protected $creditmemoService;

    /**
     * @var CreditmemoRepository
     */
    protected $creditmemoRepository;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;


    /**
     * OrderFactory constructor.
     *
     * @param Order $order
     * @param ObjectManagerInterface $objectManager
     * @param MagentoOrderRepository $magentoOrderRepository
     * @param MidtransLogger $midtransLogger
     * @param Order\Invoice $invoice
     * @param InvoiceService $invoiceService
     * @param CreditmemoFactory $creditmemoFactory
     * @param CreditmemoService $creditmemoService
     * @param CreditmemoRepository $creditmemoRepository
     * @param Transaction $transaction
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        Order $order,
        ObjectManagerInterface $objectManager,
        MagentoOrderRepository $magentoOrderRepository,
        MidtransLogger $midtransLogger,
        Order\Invoice $invoice,
        InvoiceService $invoiceService,
        CreditmemoFactory $creditmemoFactory,
        CreditmemoService $creditmemoService,
        CreditmemoRepository $creditmemoRepository,
        Transaction $transaction,
        MessageManagerInterface $messageManager,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->order = $order;
        $this->objectManager = $objectManager;
        $this->magentoOrderRepository = $magentoOrderRepository;
        $this->midtransLogger = $midtransLogger;
        $this->invoice = $invoice;
        $this->invoiceService = $invoiceService;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->transaction = $transaction;
        $this->messageManager = $messageManager;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Get order by incrementId
     *
     * @param $realOrderId
     * @return Order
     */
    public function getOrderByIncrementId($realOrderId)
    {
        return $this->order->loadByIncrementId($realOrderId);
    }

    /**
     * Get order by entity id
     *
     * @return OrderInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderByEntityId($entityId)
    {
        return $this->magentoOrderRepository->get($entityId);
    }

    /**
     * Get payment code from Order by incrementId
     *
     * @param $incrementId
     * @return string
     */
    public function getPaymentCode($incrementId)
    {
        return $this->getOrderByIncrementId($incrementId)->getPayment()->getMethod();
    }

    /**
     * Find orders from database by field key
     *
     * @param $fieldKey
     * @param $value
     * @return OrderInterface[]
     */
    public function getOrderCollection($fieldKey, $value)
    {
        $searchCriteriaBuilder = $this->objectManager->create('Magento\Framework\Api\SearchCriteriaBuilder');
        $searchCriteria = $searchCriteriaBuilder->addFilter($fieldKey, $value, 'eq')->create();
        return $this->magentoOrderRepository->getList($searchCriteria)->getItems();
    }

    /**
     * Find increment ids by quote id
     *
     * @param $quoteId
     * @return array
     */
    public function getIncrementIdsByQuoteId($quoteId)
    {
        $orderIds = [];
        $orders = $this->getOrderCollection('quote_id', $quoteId);
        foreach ($orders as $order) {
            $realOrderId = $order->getRealOrderId();
            $orderIds[] = $realOrderId;
        }
        return $orderIds;
    }

    /**
     * Save order with order repository
     *
     * @param OrderInterface $order
     * @throws Exception
     */
    public function saveOrder(OrderInterface $order)
    {
        try {
            $this->magentoOrderRepository->save($order);
        } catch (Exception $e) {
            $error_exception = "OrderRepository.class SaveOrder : " . $e;
            $this->midtransLogger->midtransError($error_exception);
        }
    }

    /**
     * Set order status, state and comment status history
     *
     * @param Order $order
     * @param $status
     * @param $order_note
     * @return void
     * @throws Exception
     */
    public function setOrderStateAndStatus(Order $order, $status, $order_note)
    {
        $order->setState($status);
        $order->setStatus($status);
        $order->addStatusToHistory($status, $order_note, false);
        $this->saveOrder($order);
    }

    /**
     * Check order is available
     *
     * @param Order $order
     * @return bool
     */
    public function canProcess(Order $order)
    {
        /**
         * Do not process if order not found,
         * if Log enable, add record to /var/log/midtrans/notification.log
         */
        if ($order->isEmpty() || $order === null) {
            $_info = "NOTIFICATION: 404 NOT FOUND - Order not found on Magento 2";
            $this->midtransLogger->midtransNotification($_info);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Set payment gateway information to Order
     *
     * @param Order $order
     * @param $trxId
     * @param $paymentType
     * @throws Exception
     */
    public function setPaymentInformation(Order $order, $trxId, $paymentType)
    {
        $order->getPayment()->setAdditionalInformation('payment_method', strtoupper($paymentType));
        $order->getPayment()->setAdditionalInformation('midtrans_trx_id', $trxId);
        $this->saveOrder($order);
    }

    /**
     * Get payment code by quote id
     *
     * @param $quoteId
     * @return string|null
     */
    public function getPaymentCodeByQuoteId($quoteId)
    {
        $paymentCode = null;
        $orders = $this->getOrderCollection('quote_id', $quoteId);
        foreach ($orders as $order) {
            $paymentCode = $order->getPayment()->getMethod();
        }
        return $paymentCode;
    }

    /**
     * Do cancel order, and set status, state, also comment status history
     *
     * @param Order $order
     * @param $status
     * @param $order_note
     * @return Order
     * @throws Exception
     */
    public function cancelOrder(Order $order, $status, $order_note)
    {
        $order->setState($status);
        $order->setStatus($status);
        $order->addStatusToHistory($status, $order_note, false);
        $order->cancel();
        $this->saveOrder($order);

        return $order;
    }

    /**
     * Check is order contain virtual product
     *
     * @param $incrementId
     * @return bool
     */
    public function isContainVirtualProduct($incrementId)
    {
        $items = $this->getOrderByIncrementId($incrementId)->getAllItems();
        foreach ($items as $item) {
            //look for virtual products
            if ($item->getProduct()->getIsVirtual()) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Do generate Invoice
     *
     * @param Order $order
     * @return InvoiceInterface|Order\Invoice
     */
    public function generateInvoice(Order $order, $midtransTrxId)
    {
        try {
            if ($order->isEmpty()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('MIDTRANS-INFO: The order no longer exists.'));
            }
            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('MIDTRANS-INFO: The order does not allow an invoice to be created.')
                );
            }

            $invoice = $this->invoiceService->prepareInvoice($order);
            if (!$invoice) {
                throw new \Magento\Framework\Exception\LocalizedException(__('MIDTRANS-INFO: We can\'t save the invoice right now.'));
            }
            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('MIDTRANS-INFO: You can\'t create an invoice without products.'));
            }

            if ($midtransTrxId) {
                $invoice->setTransactionId($midtransTrxId);
                $order->getPayment()->setLastTransId($midtransTrxId);
            }
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
            //  $invoice->pay();

            $invoice->register();
            $invoice->getOrder()->setCustomerNoteNotify(false);

            $this->invoiceRepository->save($invoice);
            $this->magentoOrderRepository->save($order);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * Create credit memo for refund from midtrans dashboard,
     * if refund from midtrans dashboard is fullRefund. create credit memo and cancel order.
     *
     * @param Order $order
     * @param $isFullRefund
     * @param $refund_note
     * @throws \Exception
     */
    public function createCreditMemo(Order $order, $isFullRefund, $refund_note)
    {
        $invoices = $order->getInvoiceCollection();
        $orderId = $order->getIncrementId();
        foreach ($invoices as $invoice) {
            $invoiceIncrementId = $invoice->getIncrementId();
        }
        if (isset($invoiceIncrementId)) {
            $orderInvoice = $this->invoice->loadByIncrementId($invoiceIncrementId);
        } else {
            $this->midtransLogger->midtransError("AbstractAction.class CreateCreditMemo:: Failed create Credit Memo with order-id " . $orderId . " Invoice not found");
        }
        if ($isFullRefund) {
            $creditMemo = $this->creditmemoFactory->createByOrder($order);
            if (isset($orderInvoice)) {
                $creditMemo->setInvoice($orderInvoice);
            } else {
                $this->midtransLogger->midtransError("AbstractAction.class CreateCreditMemo:: Failed create Credit Memo with order-id " . $orderId . " Invoice not found");
            }
            $creditMemo->setState(Order\Creditmemo::STATE_REFUNDED);
        }
        try {
            if (isset($creditMemo)) {
                $this->creditmemoRepository->save($creditMemo);
            } else {
                $this->midtransLogger->midtransError("AbstractAction.class CreateCreditMemo:: Failed create Credit Memo with order-id " . $orderId . " Invoice not found");
            }
        } catch (CouldNotSaveException $e) {
            $error_exception = "AbstractAction.class saveCreditMemo :" . $e;
            $this->midtransLogger->midtransError($error_exception);
        }
    }
}
