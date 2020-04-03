<?php

namespace Midtrans\Snap\Model\Config\Source\Payment;


use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Adapter;

use Magento\Payment\Model\MethodInterface;
use Midtrans\Snap\Gateway\Config\Config;
use Midtrans\Snap\Gateway\MidtransApi;
use Midtrans\Snap\Gateway\Transaction;
use Midtrans\Snap\Helper\Data;
use Midtrans\Snap\Logger\MidtransLogger;
use PHPUnit\Exception;

/**
 * Class AbstractPayment
 * @package Midtrans\Snap\Model\Config\Source\Payment
 */
 abstract class AbstractPayment extends Adapter
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

    protected $dataConfig;

    protected $midtransLogger;


     /**
      * AbstractPayment constructor.
      * @param ManagerInterface $eventManager
      * @param ValueHandlerPoolInterface $valueHandlerPool
      * @param PaymentDataObjectFactory $paymentDataObjectFactory
      * @param Data $dataConfig
      * @param MidtransLogger $midtransLogger
      * @param string $code
      * @param string $formBlockType
      * @param string $infoBlockType
      * @param CommandPoolInterface|null $commandPool
      * @param ValidatorPoolInterface|null $validatorPool
      * @param CommandManagerInterface|null $commandExecutor
      */
    public function __construct
    (
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        Data $dataConfig,
        MidtransLogger $midtransLogger,
        $code,
        $formBlockType,
        $infoBlockType,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null
    )
    {
        $this->dataConfig = $dataConfig;
        $this->midtransLogger = $midtransLogger;
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
    }

     /**
      * @param InfoInterface $payment
      * @param $amount
      * @return Adapter|MethodInterface|void
      * @throws LocalizedException
      */
     function refund(InfoInterface $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new LocalizedException(__('The refund action is not available.'));
        }

        $order = $payment->getOrder();
        $paymentCode = $order->getPayment()->getMethod();
        $orderId = $order->getRealOrderId();

        Config::$serverKey = $this->dataConfig->getServerKey($paymentCode);
        $transaction = new Transaction();


        // Check is full refund or partial
        $canRefundMore = $payment->getCreditmemo()->getInvoice()->canRefund();
        $isFullRefund = !$canRefundMore && (double)$order->getBaseTotalOnlineRefunded() == (double)$payment->getBaseAmountPaid();

        $refundKey = $orderId . '-' . time();
        $reasonRefund = "Refund From Magento Dashboard";
        $refundParams = [
            'refund_key'    => $refundKey,
            'amount'        => $amount,
            'reason'        => $reasonRefund
        ];

        // Request refund to Gateway
        try {
            $order->addCommentToStatusHistory('Request Refund with Refund-Key: '.$refundKey, false, false);
            $order->save();

            $response = $transaction::refund($orderId, $refundParams);
        } catch (Exception $e) {
            $error_message = strpos($e->getMessage(), '412') ? $e->getMessage() . ' Note: Refund via Midtrans only for specific payment method, please consult to your midtrans PIC for more information' : $e->getMessage();
            $this->midtransLogger->midtransError($error_message);
        }

        if ($response->status_code == 200) {
            if ($isFullRefund) {
                $refund_message = sprintf('Full Refunded %1$s | Refund-Key %2$s | %3$s', $response->refund_amount, $response->refund_key, $reasonRefund);
            } else {
                $refund_message = sprintf('Partial Refunded %1$s | Refund-Key %2$s | %3$s', $response->refund_amount, $response->refund_key, $reasonRefund);
            }
            $order->addCommentToStatusHistory($refund_message, false, false);
            $order->save();
        }
        else if ($response->status_code == 412) {
            echo "ssssss";
        }
    }
}
