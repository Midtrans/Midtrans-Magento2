<?php

namespace Midtrans\Snapspec\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Midtrans\Snap\specModel\Config\Source\Order\Status\Paymentreview;
use Magento\Sales\Model\Order;

class Snapspec extends \Magento\Payment\Model\Method\AbstractMethod
{
    const SNAPSPEC_PAYMENT_CODE = 'snapspec';
    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'snapspec';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * Payment additional info block
     *
     * @var string
     */
    protected $_formBlockType = 'Midtrans\Snapspec\Block\Form\Snapspec';

    protected $_isProduction;

    protected $orderFactory;


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->orderFactory = $orderFactory;
        parent::__construct($context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data);
    }

    public function getAmount($orderId)//\Magento\Framework\Object $payment)
    {   $orderFactory = $this->orderFactory;

        /* @var $order \Magento\Sales\Model\Order */
        $order = $orderFactory->create()->loadByIncrementId($orderId);
        return $order->getGrandTotal();
    }

    protected function getOrder($orderId)
    {
        $orderFactory = $this->orderFactory;
        return $orderFactory->create()->loadByIncrementId($orderId);
    }

    /**
     * Set order state and status
     *
     * @param string $paymentAction
     * @param \Magento\Framework\Object $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = $this->getConfigData('order_status');
        $this->_isProduction = $this->getConfigData('is_production');
        $stateObject->setStatus($state);
        $stateObject->setIsNotified(false);
    }

    /**
     * Check whether payment method can be used
     *
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote === null) {
            return false;
        }
        return parent::isAvailable($quote);
    }

    public function generateHash($login, $sum, $pass, $id = null)
    {

        $hashData = array(
            "MrchLogin" => $login,
            "OutSum" => $sum,

            "InvId" => $id,
            "pass" => $pass,
        );

        $hash = strtoupper(md5(implode(":", $hashData)));
        return $hash;
    }

    public function getPostData($orderId)
    {
        $PostData = [];
        $PostData['OutSum'] = round($this->getAmount($orderId), 2);
        $PostData['InvId'] = intval($orderId);
        $PostData['MerchantLogin'] = $this->getConfigData('merchant_id');
        $PostData['Description'] = "payment for order " . $orderId;
        $PostData['SignatureValue'] = $this->generateHash($PostData['MerchantLogin'],
            $PostData['OutSum'], $this->getConfigData('pass_word_1'), $PostData['InvId']);
        return $PostData;

    }

    public function process($responseData)
    {
        $debugData = ['response' => $responseData];
        $this->_debug($debugData);

        if (count($responseData) > 2) {
            $order = $this->getOrder(sprintf("%010s", $responseData['InvId']));

            if ($order) {
                echo $this->_processOrder($order, $responseData);
            }
        } else {
            echo "errors";
        }
    }

    protected function _processOrder(\Magento\Sales\Model\Order $order, $response)
    {
        $payment = $order->getPayment();
        try {
            $errors = array();
            $hashArray = array(
                $response["OutSum"],
                $response["InvId"],
                $this->getConfigData('pass_word_2')
            );

            $hashCurrent = strtoupper(md5(implode(":", $hashArray)));
            $correctHash = (strcmp($hashCurrent, strtoupper($response['SignatureValue'])) == 0);

            if (!$correctHash) {
                $errors[] = "Incorrect HASH (need:" . $hashCurrent . ", got:"
                    . strtoupper($response['SignatureValue']) . ") - fraud data or wrong secret Key";
                $errors[] = "Maybe success payment";
            }

            /**
             * @var $order Mage_Sales_Model_Order
             */
            $outSum = round($order->getGrandTotal(), 2);

            if ($outSum != $response["OutSum"]) {
                $errors[] = "Incorrect Amount: " . $response["OutSum"] . " (need: " . $outSum . ")";
            }


            if (!$correctHash) {
                $payment->setTransactionId($response["InvId"])->setIsTransactionClosed(0);
                $order->setStatus(Order::STATE_PAYMENT_REVIEW);
                $order->save();
                return "Ok" . $response["InvId"];
            }
        } catch (Exception $e) {
            return array("Internal error:" . $e->getMessage());
        }
    }

    public function getOrderPlaceRedirectUrl()
    {
        return 'http://www.google.com/';
    }

}
