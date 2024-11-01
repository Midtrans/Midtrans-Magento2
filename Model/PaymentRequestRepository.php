<?php

namespace Midtrans\Snap\Model;

use Exception;
use Magento\Sales\Model\Order;
use Midtrans\Snap\Helper\MidtransDataConfiguration;
use Midtrans\Snap\Helper\Utils;
use Midtrans\Snap\Model\Order\OrderRepository;

class PaymentRequestRepository
{
    /**
     * @var OrderRepository
     */
    protected $paymentOrderRepository;

    /**
     * @var MidtransDataConfiguration
     */
    protected $midtransDataConfig;

    /**
     * @var Utils
     */
    protected $utils;

    /**
     * PaymentRequestRepository constructor.
     * @param OrderRepository $paymentOrderRepository
     * @param MidtransDataConfiguration $midtransDataConfig
     * @param Utils $utils
     */
    public function __construct(OrderRepository $paymentOrderRepository, MidtransDataConfiguration $midtransDataConfig, Utils $utils)
    {
        $this->paymentOrderRepository = $paymentOrderRepository;
        $this->midtransDataConfig = $midtransDataConfig;
        $this->utils = $utils;
    }

    /**
     * do create billing address for payload request to Midtrans
     *
     * @param $order_billing_address
     * @return array
     */
    public function payloadBillingAddress($order_billing_address)
    {
        $payload_billing_address = [];
        $payload_billing_address['first_name'] = $order_billing_address->getFirstname();
        $payload_billing_address['last_name'] = $order_billing_address->getLastname();
        $payload_billing_address['address'] = $order_billing_address->getStreet()[0];
        $payload_billing_address['city'] = $order_billing_address->getCity();
        $payload_billing_address['postal_code'] = $order_billing_address->getPostcode();
        $payload_billing_address['country_code'] = $this->utils->convert_country_code($order_billing_address->getCountryId());
        $payload_billing_address['phone'] = $order_billing_address->getTelephone();
        return $payload_billing_address;
    }

    /**
     * do create shipping address for payload request to Midtrans
     *
     * @param $order_shipping_address
     * @return array
     */
    public function payloadShippingAddress($order_shipping_address)
    {
        $shipping_address = [];
        $shipping_address['first_name'] = $order_shipping_address->getFirstname();
        $shipping_address['last_name'] = $order_shipping_address->getLastname();
        $shipping_address['address'] = $order_shipping_address->getStreet()[0];
        $shipping_address['city'] = $order_shipping_address->getCity();
        $shipping_address['postal_code'] = $order_shipping_address->getPostcode();
        $shipping_address['phone'] = $order_shipping_address->getTelephone();
        $shipping_address['country_code'] = $this->utils->convert_country_code($order_shipping_address->getCountryId());
        return $shipping_address;
    }

    /**
     * do create item details for payload request to Midtrans
     *
     * @param Order $order
     * @param $isMultishipping
     * @return array
     */
    public function payloadItemDetail(Order $order, $isMultishipping)
    {
        $item_details = [];
        $items = $order->getAllItems();

        $itemPrefix = '';
        if ($isMultishipping) {
            $itemPrefix = ' For Order :' . $order->getIncrementId();
        }
        foreach ($items as $newItem) {
            $newItem = [
                'id' => 'ITEM-ID ' . $newItem->getProductId() . $itemPrefix,
                'price' => (string)round($newItem->getPrice()),
                'quantity' => (string)round($newItem->getQtyOrdered()),
                'name' => $this->utils->sanitizeItemName($newItem->getName())
            ];
            $item_details[] = $newItem;
        }

        if ($order->getDiscountAmount() != 0) {
            $couponItem = [
                'id' => 'DISCOUNT' . $itemPrefix,
                'price' => round($order->getDiscountAmount()),
                'quantity' => 1,
                'name' => 'DISCOUNT'
            ];
            $item_details[] = $couponItem;
        }

        if ($order->getShippingAmount() > 0) {
            $shipping_item = [
                'id' => 'SHIPPING' . $itemPrefix,
                'price' => round($order->getShippingAmount()),
                'quantity' => 1,
                'name' => 'Shipping Cost'
            ];
            $item_details[] = $shipping_item;
        }

        if ($order->getShippingTaxAmount() > 0) {
            $shipping_tax_item = [
                'id' => 'SHIPPING_TAX' . $itemPrefix,
                'price' => round($order->getShippingTaxAmount()),
                'quantity' => 1,
                'name' => 'Shipping Tax'
            ];
            $item_details[] = $shipping_tax_item;
        }

        if ($order->getTaxAmount() > 0) {
            $tax_item = [
                'id' => 'TAX' . $itemPrefix,
                'price' => round($order->getTaxAmount()),
                'quantity' => 1,
                'name' => 'Tax'
            ];
            $item_details[] = $tax_item;
        }

        if ($order->getBaseGiftCardsAmount() != 0) {
            $giftcardAmount = [
                'id' => 'GIFTCARD' . $itemPrefix,
                'price' => round($order->getBaseGiftCardsAmount() * -1),
                'quantity' => 1,
                'name' => 'GIFTCARD'
            ];
            $item_details[] = $giftcardAmount;
        }

        if ($order->getBaseCustomerBalanceAmount() != 0) {
            $balancAmount = [
                'id' => 'STORE CREDIT' . $itemPrefix,
                'price' => round($order->getBaseCustomerBalanceAmount() * -1),
                'quantity' => 1,
                'name' => 'STORE CREDIT'
            ];
            $item_details[] = $balancAmount;
        }

        if ($order->getPaymentFee() != 0) {
            $paymentFee = [
                'id' => 'PAYMENT FEE' . $itemPrefix,
                'price' => round($order->getPaymentFee()),
                'quantity' => 1,
                'name' => 'PAYMENT FEE'
            ];
            $item_details[] = $paymentFee;
        }
        return $item_details;
    }

    /**
     * do create payload body request for get Midtrans Snap token
     *
     * @param $config
     * @param $paymentCode
     * @param null $order
     * @param null $multishipping
     * @return array payload
     * @throws Exception
     */
    public function getPayload(
        $config,
        $paymentCode,
        $order = null,
        $multishipping = null
    ) {
        $payloads = [];
        $customer_details = [];
        $totalPrice = 0;

        $merchantId = $this->midtransDataConfig->getMerchantId($paymentCode);
        /**
         * Check if request for multishipping
         */
        if (isset($multishipping)) {
            /**
             * 1. find incrementIds by quote id from request
             */
            $quoteId = $multishipping['quote_id'];
            $incrementIds = $this->paymentOrderRepository->getIncrementIdsByQuoteId($quoteId);
            $midtransOrderId = "multishipping-" . $quoteId;

            $item_details = [];
            foreach ($incrementIds as $orderId) {
                $order  = $this->paymentOrderRepository->getOrderByIncrementId($orderId);
                $payment = $order->getPayment();
                $payment->setAdditionalInformation('payment_gateway', 'Midtrans');
                $payment->setAdditionalInformation('merchant_id', $merchantId);
                $payment->setAdditionalInformation('midtrans_order_id', $midtransOrderId);
                $this->paymentOrderRepository->saveOrder($order);
                foreach ($this->payloadItemDetail($order, true) as $item) {
                    $item_details[] = $item;
                }
            }

            /**
             * Set items cart to item details object for payload
             */
            foreach ($item_details as $item) {
                $totalPrice += $item['price'] * $item['quantity'];
            }

            /**
             * Set billing address order to billing address object for payload
             */
            $order_billing_address = $order->getBillingAddress();
            $billing_address = $this->payloadBillingAddress($order_billing_address);

            $customer_details['first_name'] = $order_billing_address->getFirstname();
            $customer_details['last_name'] = $order_billing_address->getLastname();
            $customer_details['email'] = $order_billing_address->getEmail();
            $customer_details['phone'] = $order_billing_address->getTelephone();
            $customer_details['billing_address'] = $billing_address;

            /**
             * Initial Payload for request to Midtrans Payment
             */
            $transaction_details['order_id'] = $midtransOrderId;
            $transaction_details['gross_amount'] = $totalPrice;

            $payloads['transaction_details'] = $transaction_details;
            $payloads['item_details'] = $item_details;
        } else {
            /**
             * For regular order
             */
            $incrementId = $order->getIncrementId();

            // Get Billing address from order
            $order_billing_address = $order->getBillingAddress();

            // Set additional payment info
            $payment = $order->getPayment();
            $payment->setAdditionalInformation('payment_gateway', 'Midtrans');
            $payment->setAdditionalInformation('merchant_id', $merchantId);
            $payment->setAdditionalInformation('midtrans_order_id', $incrementId);
            $this->paymentOrderRepository->saveOrder($order);

            /**
             * Set billing address order to billing address object for payload
             */
            $billing_address = $this->payloadBillingAddress($order_billing_address);

            if (!$this->paymentOrderRepository->isContainVirtualProduct($incrementId)) {
                //Get Shipping Address from order
                $order_shipping_address = $order->getShippingAddress();

                /**
                 * Set shipping address order to shipping address object for payload
                 */
                $shipping_address = $this->payloadShippingAddress($order_shipping_address);
            }

            /**
             * Set items cart to item details object for payload
             */
            $item_details = $this->payloadItemDetail($order, false);

            /**
             * Set Customer details object for payload
             */

            $customer_details['billing_address'] = $billing_address;
            if (isset($shipping_address)) {
                $customer_details['shipping_address'] = $shipping_address;
            }
            $customer_details['first_name'] = $order_billing_address->getFirstname();
            $customer_details['last_name'] = $order_billing_address->getLastname();
            $customer_details['email'] = $order_billing_address->getEmail();
            $customer_details['phone'] = $order_billing_address->getTelephone();
            $customer_details['billing_address'] = $billing_address;

            foreach ($item_details as $item) {
                $totalPrice += $item['price'] * $item['quantity'];
            }

            /**
             * Initial Payload for request to Midtrans Payment
             */
            $transaction_details = [];
            $transaction_details['order_id'] = $order->getIncrementId();
            $transaction_details['gross_amount'] = $totalPrice;

            $payloads['transaction_details'] = $transaction_details;
            $payloads['item_details'] = $item_details;
        }

        $payloads['customer_details'] = $customer_details;

        if ($paymentCode == 'snap') {
            $credit_card = $this->getSnapCardConfig($config);
            if ($credit_card != null) {
                $payloads['credit_card'] = $credit_card;
            }
        } elseif ($paymentCode == 'specific') {
            $credit_card = $this->getSpecificCardConfig($config);
            $payloads['credit_card'] = $credit_card;
            $enablePayment = $config['enabled_payments'];
            if (!empty($enablePayment)) {
                $enabled_payments = explode(',', $enablePayment);
                $payloads['enabled_payments'] = $enabled_payments;
            }
        } elseif ($paymentCode == 'installment') {
            $minimalAmount = $config['minimal_amount'];
            $credit_card = $this->getInstallmentCardConfig($config, $minimalAmount, $totalPrice);
            $payloads['enabled_payments'] = ['credit_card'];
            $payloads['credit_card'] = $credit_card;
        } elseif ($paymentCode == 'offline') {
            $minimalAmount = $config['minimal_amount'];
            $credit_card = $this->getOfflineCardConfig($config, $minimalAmount, $totalPrice);
            $payloads['enabled_payments'] = ['credit_card'];
            $payloads['credit_card'] = $credit_card;
        }

        if ($config['one_click']) {
            $payloads['user_id'] = crypt($order_billing_address->getEmail(), $this->midtransDataConfig->getServerKey($paymentCode));
        }
        if (isset($config['custom_expiry'])) {
            $customExpiry = explode(" ", $config['custom_expiry']);
            $expiry_unit = $customExpiry[1];
            $expiry_duration = (int)$customExpiry[0];

            $payloads['expiry'] = [
                'unit' => $expiry_unit,
                'duration' => (int)$expiry_duration
            ];
        }
        return $payloads;
    }

    /**
     * Get credit card config for payment code snap
     *
     * @param $config
     * @return array config for snap credit card
     */
    protected function getSnapCardConfig($config)
    {
        $snap_credit_card = [];
        $bank = $config['bank'];
        if (!empty($bank)) {
            $snap_credit_card['bank'] = $bank;
        }
        $binNumber = $config['bin'];
        if (!empty($binNumber)) {
            $bin = explode(',', $binNumber);
            $snap_credit_card['whitelist_bins'] = $bin;
        }
        $oneClick = $config['one_click'];
        if ($oneClick) {
            $snap_credit_card['save_card'] = true;
        }
        return $snap_credit_card;
    }

    /**
     * Get credit card config for payment code specific
     *
     * @param $config
     * @return array
     */
    protected function getSpecificCardConfig($config)
    {
        $specific_credit_card = [];
        $bank = $config['bank'];
        if (!empty($bank)) {
            $specific_credit_card['bank'] = $bank;
        }
        $binNumber = $config['bin'];
        if (!empty($binNumber)) {
            $bin = explode(',', $binNumber);
            $specific_credit_card['whitelist_bins'] = $bin;
        }
        $oneClick = $config['one_click'];
        if ($oneClick) {
            $specific_credit_card['save_card'] = true;
        }
        return $specific_credit_card;
    }

    /**
     * Get credit card config for payment code installment
     *
     * @param $config
     * @param $minAmount
     * @param $totalPrice
     * @return array
     */
    protected function getInstallmentCardConfig($config, $minAmount, $totalPrice)
    {
        $installment_credit_card = [];
        $oneClick = $config['one_click'];
        if ($oneClick) {
            $installment_credit_card['save_card'] = true;
        }
        if ($totalPrice >= $minAmount) {
            $terms = [3, 6, 9, 12, 15, 18, 21, 24, 27, 30, 33, 36];
            $installment = [];
            $installment['required'] = true;
            $installment['terms'] = [
                'bca' => $terms,
                'bri' => $terms,
                'maybank' => $terms,
                'mega' => $terms,
                'bni' => $terms,
                'mandiri' => $terms,
                'cimb' => $terms
            ];
            $installment_credit_card['installment'] = $installment;
        }
        return $installment_credit_card;
    }

    /**
     * Get credit card config for payment code offline
     *
     * @param $config
     * @param $minAmount
     * @param $totalPrice
     * @return array
     */
    protected function getOfflineCardConfig($config, $minAmount, $totalPrice)
    {
        $offline_credit_card = [];
        $bank = $config['bank'];
        if (!empty($bank)) {
            $offline_credit_card['bank'] = $bank;
        }

        $oneClick = $config['one_click'];
        if ($oneClick) {
            $offline_credit_card['save_card'] = true;
        }

        if ($totalPrice >= $minAmount) {
            $installTerms = $config['terms'];
            $termsStr = explode(',', $installTerms);
            $terms = [];
            foreach ($termsStr as $termStr) {
                $terms[] = (int)$termStr;
            }

            $installment = [];
            $installment['required'] = true;
            $installment['terms'] = [
                'offline' => $terms
            ];

            $offline_credit_card['installment'] = $installment;

            //add bin filter
            $binFilter = $config['bin'];
            if (!empty($binFilter)) {
                $whitelist_bin = explode(',', $binFilter);
                $offline_credit_card['whitelist_bins'] = $whitelist_bin;
            }
        }
        return $offline_credit_card;
    }
}
