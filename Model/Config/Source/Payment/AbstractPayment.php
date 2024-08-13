<?php

namespace Midtrans\Snap\Model\Config\Source\Payment;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Midtrans\Snap\Gateway\Config\Config;
use Midtrans\Snap\Gateway\Transaction;
use Midtrans\Snap\Gateway\Utility\PaymentUtils;
use Midtrans\Snap\Helper\MidtransDataConfiguration;
use Midtrans\Snap\Logger\MidtransLogger;

/**
 * Class AbstractPayment
 */
class AbstractPayment extends Adapter
{
    /**
     * @var string
     */
    public $code;

    /**
     * @var bool
     */
    protected $isGateway = true;
    /**
     * @var bool
     */
    protected $canRefund = true;
    /**
     * @var bool
     */
    protected $canCapture = true;
    /**
     * @var bool
     */
    protected $canRefundInvoicePartial = true;

    /**
     * @var string
     */
    public $formBlockType;

    /**
     * @var string
     */
    public $infoBlockType;

    /**
     * @var MidtransDataConfiguration
     */
    protected $dataConfig;

    /**
     * @var MidtransLogger
     */
    protected $midtransLogger;

    /**
     * @var UrlInterface
     */
    public $urlInterface;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * AbstractPayment constructor.
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param MidtransDataConfiguration $dataConfig
     * @param UrlInterface $urlInterface
     * @param StoreManagerInterface $storeManager
     * @param MidtransLogger $midtransLogger
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param CommandPoolInterface|null $commandPool
     * @param ValidatorPoolInterface|null $validatorPool
     * @param CommandManagerInterface|null $commandExecutor
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        MidtransDataConfiguration $dataConfig,
        UrlInterface $urlInterface,
        StoreManagerInterface $storeManager,
        MidtransLogger $midtransLogger,
        string $code,
        string $formBlockType,
        string $infoBlockType,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null
    ) {
        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            $validatorPool,
            $commandExecutor
        );
        $this->dataConfig = $dataConfig;
        $this->urlInterface = $urlInterface;
        $this->storeManager = $storeManager;
        $this->midtransLogger = $midtransLogger;
    }

    /**
     * Function to handle refund from Magento dashboard
     *
     * @param InfoInterface $payment
     * @param $amount
     * @return Adapter|MethodInterface|void
     * @throws LocalizedException
     */
    public function refund(InfoInterface $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new LocalizedException(__('The refund action is not available.'));
        }

        /* Override notification, if override notification from admin setting is active (default is active) */
        if ($this->dataConfig->isOverrideNotification() && $this->dataConfig->getNotificationEndpoint() != null) {
            Config::$overrideNotifUrl = $this->dataConfig->getNotificationEndpoint();
        }

        $order = $payment->getOrder();
        $paymentCode = $order->getPayment()->getMethod();
        $midtransOrderId = $payment->getAdditionalInformation('midtrans_order_id');
        $transactionId = $payment ->getAdditionalInformation('midtrans_trx_id');
        $paymentMethod = $payment->getAdditionalInformation('payment_method');
        $orderId = $order->getIncrementId();

        Config::$serverKey = $this->dataConfig->getServerKey($paymentCode);
        Config::$isProduction = $this->dataConfig->isProduction();

        $transaction = new Transaction();

        if (strpos($midtransOrderId, 'multishipping-') !== false) {
            $refundKey = $midtransOrderId . '-' . time();
        } else {
            $refundKey = 'regular-' . $midtransOrderId . '-' . time();
        }

        $reasonRefund = "Refund " . (double)$amount . ", " . $refundKey . ", from Magento dashboard order :::" . $orderId;
        $refundParams = [
            'refund_key' => $refundKey,
            'amount' => $amount,
            'reason' => $reasonRefund
        ];

        /*
         * Request refund to midtrans
         */
        if (PaymentUtils::isOpenApi($paymentMethod)){
            $response = $transaction::refundWithSnapBi($transactionId, $refundParams);
        } else {
            $response = $transaction::refund($midtransOrderId, $refundParams);
        }


        if (isset($response)) {
            if (isset($response->status_code)) {
                if ($response->status_code == 200) {
                    $order->addStatusToHistory(Order::STATE_PROCESSING, $reasonRefund, false);
                    $order->save();
                    if (PaymentUtils::isOpenApi($paymentMethod)) {
                        $payment->setTransactionId($response->order_id);
                        $payment->setParentTransactionId($response->order_id);
                    } else {
                        $payment->setTransactionId($response->refund_key);
                        $payment->setParentTransactionId($response->transaction_id);
                    }
                    $payment->setIsTransactionClosed(1);
                    $payment->setShouldCloseParentTransaction(!$this->canRefund());
                } else {
                    $this->midtransLogger->midtransRequest('RefundRequest: ' . print_r($response, true));
                    $message = isset($response->status_message) ? $response->status_message : "Something went wrong..";
                    throw new LocalizedException(__("Oops, Refund request failed :" . $message));
                }
            }
        } else {
            throw new LocalizedException(__("Oops, Refund request failed. Due to no response received. Please try again later."));
        }
    }

    public function getCode()
    {
        return parent::getCode();
    }

}
