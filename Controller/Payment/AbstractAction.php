<?php

namespace Midtrans\Snap\Controller\Payment;

use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Sales\Model\Service\InvoiceService;
use Midtrans\Snap\Helper\Data;
use Midtrans\Snap\Logger\MidtransLogger;

/**
 * Class AbstractAction to handle basic action order
 */
abstract class AbstractAction extends Action
{
    /**
     * @var SessionManagerInterface
     */
    protected $_coreSession;
    /**
     * @var Data
     */
    protected $data;
    /**
     * @var Session
     */
    protected $_checkoutSession;
    /**
     * @var Order
     */
    protected $_order;
    /**
     * @var
     */
    protected $_scopeConfig;
    /**
     * @var Session\SuccessValidator
     */
    protected $_successValidator;
    /**
     * @var PageFactory
     */
    protected $_pageFactory;
    /**
     * @var InvoiceService
     */
    protected $_invoiceService;
    /**
     * @var
     */
    protected $_creditMemoService;
    /**
     * @var
     */
    protected $_orderHistory;

    protected $_transaction;

    protected $_resourceModel;

    protected $_orderRepository;

    protected $_invoice;

    protected $_creditmemoFactory;

    protected $_creditmemoService;

    protected $_creditmemoRepository;

    protected $_midtransLogger;

    protected $registry;

    /**
     * AbstractAction constructor.
     * @param Context $context
     * @param SessionManagerInterface $coreSession
     * @param Session $checkoutSession
     * @param Order $order
     * @param Session\SuccessValidator $successValidator
     * @param Data $data
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
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $coreSession,
        Session $checkoutSession,
        Order $order,
        Session\SuccessValidator $successValidator,
        Data $data,
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
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->_coreSession = $coreSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_order = $order;
        $this->data = $data;
        $this->_successValidator = $successValidator;
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
    }

    /**
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->_checkoutSession->getLastRealOrder()->getIncrementId();
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->_checkoutSession->getLastRealOrder()->getId();
    }

    /**
     * @return Order
     */
    public function getQuote()
    {
        return $this->_order->loadByIncrementId($this->getOrderIncrementId());
    }

    /**
     * @param $orderId
     * @return Order
     */
    public function getQuoteByOrderId($orderId)
    {
        return $this->_order->loadByIncrementId($orderId);
    }

    public function getQuoteByTransactionId($trxId)
    {
        $order = $this->_order->getData('ext_order_id', $trxId);
        return $order;
    }

    /**
     * @return Data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        $payment = $this->getQuote()->getPayment();
        return $payment->getMethod();
    }

    /**
     * get billing address from quote order
     *
     * @return \Magento\Sales\Api\Data\OrderAddressInterface|null
     */
    public function getBillingAddress()
    {
        return $this->getQuote()->getBillingAddress();
    }

    /**
     * get shipping address from quote order
     *
     * @return Order\Address|null
     */
    public function getShippingAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }

    /**
     * get all items from quote order
     *
     * @return Order\Item[]
     */
    public function getAllItems()
    {
        return $this->getQuote()->getAllItems();
    }

    /**
     * get shipping amount from quote order
     *
     * @return float|null
     */
    public function getShippingAmount()
    {
        return $this->getQuote()->getShippingAmount();
    }

    /**
     * do create invoice for order
     *
     * @param $order
     * @throws \Exception
     */
    public function createInvoice($order)
    {
        try {
            $newInvoice = $this->_invoiceService->prepareInvoice($order);
        } catch (LocalizedException $e) {
            error_reporting($e);
            $this->_midtransLogger->midtransError($e->getMessage());
        } catch (\Exception $e) {
            $this->_midtransLogger->midtransError($e->getMessage());
        }
        try {
            $newInvoice->register();
            $newInvoice->canCapture();
            $newInvoice->capture();
            $newInvoice->setIsUsedForRefund(1);
        } catch (LocalizedException $e) {
            $this->_midtransLogger->midtransError($e->getMessage());
        }
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
        $payload_billing_address['country_code'] = $this->convert_country_code($order_billing_address->getCountryId());
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
        $shipping_address['country_code'] = $this->convert_country_code($order_shipping_address->getCountryId());
        return $shipping_address;
    }

    /**
     * do create item details for payload request to Midtrans
     *
     * @param $items
     * @return array
     */
    public function payloadItemDetail($items)
    {
        $item_details = [];
        foreach ($items as $each) {
            $item = [
                'id' => $each->getProductId(),
                'price' => (string)round($each->getPrice()),
                'quantity' => (string)round($each->getQtyOrdered()),
                'name' => $this->repString($this->getName($each->getName()))
            ];
            $item_details[] = $item;
        }

        if ($this->getQuote()->getDiscountAmount() != 0) {
            $couponItem = [
                'id' => 'DISCOUNT',
                'price' => round($this->getQuote()->getDiscountAmount()),
                'quantity' => 1,
                'name' => 'DISCOUNT'
            ];
            $item_details[] = $couponItem;
        }

        if ($this->getShippingAmount() > 0) {
            $shipping_item = [
                'id' => 'SHIPPING',
                'price' => round($this->getShippingAmount()),
                'quantity' => 1,
                'name' => 'Shipping Cost'
            ];
            $item_details[] = $shipping_item;
        }

        if ($this->getQuote()->getShippingTaxAmount() > 0) {
            $shipping_tax_item = [
                'id' => 'SHIPPING_TAX',
                'price' => round($this->getQuote()->getShippingTaxAmount()),
                'quantity' => 1,
                'name' => 'Shipping Tax'
            ];
            $item_details[] = $shipping_tax_item;
        }

        if ($this->getQuote()->getTaxAmount() > 0) {
            $tax_item = [
                'id' => 'TAX',
                'price' => round($this->getQuote()->getTaxAmount()),
                'quantity' => 1,
                'name' => 'Tax'
            ];
            $item_details[] = $tax_item;
        }

        if ($this->getQuote()->getBaseGiftCardsAmount() != 0) {
            $giftcardAmount = [
                'id' => 'GIFTCARD',
                'price' => round($this->getQuote()->getBaseGiftCardsAmount() * -1),
                'quantity' => 1,
                'name' => 'GIFTCARD'
            ];
            $item_details[] = $giftcardAmount;
        }

        if ($this->getQuote()->getBaseCustomerBalanceAmount() != 0) {
            $balancAmount = [
                'id' => 'STORE CREDIT',
                'price' => round($this->getQuote()->getBaseCustomerBalanceAmount() * -1),
                'quantity' => 1,
                'name' => 'STORE CREDIT'
            ];
            $item_details[] = $balancAmount;
        }
        return $item_details;
    }

    /**
     * do create payload body request for Midtrans get token
     *
     * @param $config
     * @return array payload
     */
    public function getPayload($config)
    {
        $this->setValue($this->getOrderIncrementId());

        // Get Billing address from order
        $order_billing_address = $this->getBillingAddress();

        /**
         * Set billing address order to billing address object for payload
         */
        $billing_address = $this->payloadBillingAddress($order_billing_address);

        if (!$this->isContainDownloadableProduct()) {
            //Get Shipping Address from order
            $order_shipping_address = $this->getShippingAddress();

            /**
             * Set shipping address order to shipping address object for payload
             */
            $shipping_address = $this->payloadShippingAddress($order_shipping_address);
        }

        //Get Item Cart from order
        $items = $this->getAllItems();

        /**
         * Set items cart to item details object for payload
         */
        $item_details = $this->payloadItemDetail($items);

        /**
         * Set Customer details object for payload
         */
        $customer_details = [];
        $customer_details['billing_address'] = $billing_address;
        if (isset($shipping_address)) {
            $customer_details['shipping_address'] = $shipping_address;
        }
        $customer_details['first_name'] = $order_billing_address->getFirstname();
        $customer_details['last_name'] = $order_billing_address->getLastname();
        $customer_details['email'] = $order_billing_address->getEmail();
        $customer_details['phone'] = $order_billing_address->getTelephone();
        $customer_details['billing_address'] = $billing_address;

        $totalPrice = 0;
        foreach ($item_details as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        /**
         * Initial Payload for request to Midtrans Payment
         */
        $transaction_details = [];
        $transaction_details['order_id'] = $this->getOrderIncrementId();
        $transaction_details['gross_amount'] = $totalPrice;

        $payloads = [];
        $payloads['transaction_details'] = $transaction_details;
        $payloads['item_details'] = $item_details;
        $payloads['customer_details'] = $customer_details;

        $paymentCode = $this->getCode();

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
            $payloads['user_id'] = crypt($order_billing_address->getEmail(), $this->data->getServerKey($paymentCode));
        }
        if (!empty($customExpiry)) {
            $customExpiry = explode(" ", $customExpiry);
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
     * Get credit card config for payment code installment online
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
     * Get Credit Card option for offline installment payment
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

    /**
     * @param $country_code
     * @return mixed
     */
    public function convert_country_code($country_code)
    {
        // 3 digits country codes
        $cc_three = [
            'AF' => 'AFG',
            'AX' => 'ALA',
            'AL' => 'ALB',
            'DZ' => 'DZA',
            'AD' => 'AND',
            'AO' => 'AGO',
            'AI' => 'AIA',
            'AQ' => 'ATA',
            'AG' => 'ATG',
            'AR' => 'ARG',
            'AM' => 'ARM',
            'AW' => 'ABW',
            'AU' => 'AUS',
            'AT' => 'AUT',
            'AZ' => 'AZE',
            'BS' => 'BHS',
            'BH' => 'BHR',
            'BD' => 'BGD',
            'BB' => 'BRB',
            'BY' => 'BLR',
            'BE' => 'BEL',
            'PW' => 'PLW',
            'BZ' => 'BLZ',
            'BJ' => 'BEN',
            'BM' => 'BMU',
            'BT' => 'BTN',
            'BO' => 'BOL',
            'BQ' => 'BES',
            'BA' => 'BIH',
            'BW' => 'BWA',
            'BV' => 'BVT',
            'BR' => 'BRA',
            'IO' => 'IOT',
            'VG' => 'VGB',
            'BN' => 'BRN',
            'BG' => 'BGR',
            'BF' => 'BFA',
            'BI' => 'BDI',
            'KH' => 'KHM',
            'CM' => 'CMR',
            'CA' => 'CAN',
            'CV' => 'CPV',
            'KY' => 'CYM',
            'CF' => 'CAF',
            'TD' => 'TCD',
            'CL' => 'CHL',
            'CN' => 'CHN',
            'CX' => 'CXR',
            'CC' => 'CCK',
            'CO' => 'COL',
            'KM' => 'COM',
            'CG' => 'COG',
            'CD' => 'COD',
            'CK' => 'COK',
            'CR' => 'CRI',
            'HR' => 'HRV',
            'CU' => 'CUB',
            'CW' => 'CUW',
            'CY' => 'CYP',
            'CZ' => 'CZE',
            'DK' => 'DNK',
            'DJ' => 'DJI',
            'DM' => 'DMA',
            'DO' => 'DOM',
            'EC' => 'ECU',
            'EG' => 'EGY',
            'SV' => 'SLV',
            'GQ' => 'GNQ',
            'ER' => 'ERI',
            'EE' => 'EST',
            'ET' => 'ETH',
            'FK' => 'FLK',
            'FO' => 'FRO',
            'FJ' => 'FJI',
            'FI' => 'FIN',
            'FR' => 'FRA',
            'GF' => 'GUF',
            'PF' => 'PYF',
            'TF' => 'ATF',
            'GA' => 'GAB',
            'GM' => 'GMB',
            'GE' => 'GEO',
            'DE' => 'DEU',
            'GH' => 'GHA',
            'GI' => 'GIB',
            'GR' => 'GRC',
            'GL' => 'GRL',
            'GD' => 'GRD',
            'GP' => 'GLP',
            'GT' => 'GTM',
            'GG' => 'GGY',
            'GN' => 'GIN',
            'GW' => 'GNB',
            'GY' => 'GUY',
            'HT' => 'HTI',
            'HM' => 'HMD',
            'HN' => 'HND',
            'HK' => 'HKG',
            'HU' => 'HUN',
            'IS' => 'ISL',
            'IN' => 'IND',
            'ID' => 'IDN',
            'IR' => 'RIN',
            'IQ' => 'IRQ',
            'IE' => 'IRL',
            'IM' => 'IMN',
            'IL' => 'ISR',
            'IT' => 'ITA',
            'CI' => 'CIV',
            'JM' => 'JAM',
            'JP' => 'JPN',
            'JE' => 'JEY',
            'JO' => 'JOR',
            'KZ' => 'KAZ',
            'KE' => 'KEN',
            'KI' => 'KIR',
            'KW' => 'KWT',
            'KG' => 'KGZ',
            'LA' => 'LAO',
            'LV' => 'LVA',
            'LB' => 'LBN',
            'LS' => 'LSO',
            'LR' => 'LBR',
            'LY' => 'LBY',
            'LI' => 'LIE',
            'LT' => 'LTU',
            'LU' => 'LUX',
            'MO' => 'MAC',
            'MK' => 'MKD',
            'MG' => 'MDG',
            'MW' => 'MWI',
            'MY' => 'MYS',
            'MV' => 'MDV',
            'ML' => 'MLI',
            'MT' => 'MLT',
            'MH' => 'MHL',
            'MQ' => 'MTQ',
            'MR' => 'MRT',
            'MU' => 'MUS',
            'YT' => 'MYT',
            'MX' => 'MEX',
            'FM' => 'FSM',
            'MD' => 'MDA',
            'MC' => 'MCO',
            'MN' => 'MNG',
            'ME' => 'MNE',
            'MS' => 'MSR',
            'MA' => 'MAR',
            'MZ' => 'MOZ',
            'MM' => 'MMR',
            'NA' => 'NAM',
            'NR' => 'NRU',
            'NP' => 'NPL',
            'NL' => 'NLD',
            'AN' => 'ANT',
            'NC' => 'NCL',
            'NZ' => 'NZL',
            'NI' => 'NIC',
            'NE' => 'NER',
            'NG' => 'NGA',
            'NU' => 'NIU',
            'NF' => 'NFK',
            'KP' => 'MNP',
            'NO' => 'NOR',
            'OM' => 'OMN',
            'PK' => 'PAK',
            'PS' => 'PSE',
            'PA' => 'PAN',
            'PG' => 'PNG',
            'PY' => 'PRY',
            'PE' => 'PER',
            'PH' => 'PHL',
            'PN' => 'PCN',
            'PL' => 'POL',
            'PT' => 'PRT',
            'QA' => 'QAT',
            'RE' => 'REU',
            'RO' => 'SHN',
            'RU' => 'RUS',
            'RW' => 'EWA',
            'BL' => 'BLM',
            'SH' => 'SHN',
            'KN' => 'KNA',
            'LC' => 'LCA',
            'MF' => 'MAF',
            'SX' => 'SXM',
            'PM' => 'SPM',
            'VC' => 'VCT',
            'SM' => 'SMR',
            'ST' => 'STP',
            'SA' => 'SAU',
            'SN' => 'SEN',
            'RS' => 'SRB',
            'SC' => 'SYC',
            'SL' => 'SLE',
            'SG' => 'SGP',
            'SK' => 'SVK',
            'SI' => 'SVN',
            'SB' => 'SLB',
            'SO' => 'SOM',
            'ZA' => 'ZAF',
            'GS' => 'SGS',
            'KR' => 'KOR',
            'SS' => 'SSD',
            'ES' => 'ESP',
            'LK' => 'LKA',
            'SD' => 'SDN',
            'SR' => 'SUR',
            'SJ' => 'SJM',
            'SZ' => 'SWZ',
            'SE' => 'SWE',
            'CH' => 'CHE',
            'SY' => 'SYR',
            'TW' => 'TWN',
            'TJ' => 'TJK',
            'TZ' => 'TZA',
            'TH' => 'THA',
            'TL' => 'TLS',
            'TG' => 'TGO',
            'TK' => 'TKL',
            'TO' => 'TON',
            'TT' => 'TTO',
            'TN' => 'TUN',
            'TR' => 'TUR',
            'TM' => 'TKM',
            'TC' => 'TCA',
            'TV' => 'TUV',
            'UG' => 'UGA',
            'UA' => 'UKR',
            'AE' => 'ARE',
            'GB' => 'GBR',
            'US' => 'USA',
            'UY' => 'URY',
            'UZ' => 'UZB',
            'VU' => 'VUT',
            'VA' => 'VAT',
            'VE' => 'VEN',
            'VN' => 'VNM',
            'WF' => 'WLF',
            'EH' => 'ESH',
            'WS' => 'WSM',
            'YE' => 'YEM',
            'ZM' => 'ZMB',
            'ZW' => 'ZWE'
        ];
        // Check if country code exists
        if (isset($cc_three[$country_code]) && $cc_three[$country_code] != '') {
            $country_code = $cc_three[$country_code];
        }
        return $country_code;
    }

    /**
     * @param $order_id
     */
    public function setValue($order_id)
    {
        $this->_coreSession->start();
        $this->_coreSession->setMessage($order_id);
    }

    /**
     * @param $str
     * @return string|string[]|null
     */
    private function repString($str)
    {
        return preg_replace("/[^a-zA-Z0-9]+/", " ", $str);
    }

    /**
     * @param $s
     * @return false|string
     */
    private function getName($s)
    {
        $max_length = 20;
        if (strlen($s) > $max_length) {
            $offset = ($max_length - 3) - strlen($s);
            $s = substr($s, 0, strrpos($s, ' ', $offset));
        }
        return $s;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $this->_coreSession->start();
        return $this->_coreSession->getMessage();
    }

    /**
     * @return mixed
     */
    public function unSetValue()
    {
        $this->_coreSession->start();
        return $this->_coreSession->unsMessage();
    }

    /**
     * Do generate Invoice
     *
     * @param $orderId
     * @param $order_state
     * @param $payment_type
     * @param $order_note
     * @return InvoiceInterface|Order\Invoice
     */
    public function generateInvoice($orderId, $payment_type)
    {
        try {
            $order = $this->getQuoteByOrderId($orderId);
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('MIDTRANS-INFO: The order no longer exists.'));
            }
            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('MIDTRANS-INFO: The order does not allow an invoice to be created.')
                );
            }

            $invoice = $this->_invoiceService->prepareInvoice($order);
            if (!$invoice) {
                throw new \Magento\Framework\Exception\LocalizedException(__('MIDTRANS-INFO: We can\'t save the invoice right now.'));
            }
            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('MIDTRANS-INFO: You can\'t create an invoice without products.'));
            }
            if ($payment_type == 'gopay' || $payment_type == 'credit_card') {
                $invoice->setTransactionId($orderId);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
            } else {
                $invoice->pay();
            }
            $invoice->register();
            $invoice->getOrder()->setCustomerNoteNotify(false);

            //Save Invoice
            $transactionSave = $this->_transaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();

            // send invoice emails, If you want to stop mail disable below try/catch code
//            try {
//                $this->invoiceSender->send($invoice);
//            } catch (\Exception $e) {
//                $this->messageManager->addError(__('MIDTRANS-INFO: We can\'t send the invoice email right now.'));
//            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $invoice;
    }

    /**
     * Do cancel order, and set status, state, also comment status history
     *
     * @param $orderId
     * @param $status
     * @param $order_note
     * @return Order
     * @throws \Exception
     */
    public function cancelOrder($orderId, $status, $order_note)
    {
        $order = $this->getQuoteByOrderId($orderId);
        $order->setState($status);
        $order->setStatus($status);
        $order->addStatusToHistory($status, $order_note, false);
        $order->cancel();
        $this->saveOrder($order);
        return $order;
    }

    /**
     * Set order status, state and comment status history
     *
     * @param $orderId
     * @param $status
     * @param $order_note
     * @param $trxId
     * @return void
     * @throws \Exception
     */
    public function setOrderStateAndStatus($orderId, $status, $order_note)
    {
        $order = $this->getQuoteByOrderId($orderId);
        $order->setState($status);
        $order->setStatus($status);
        $order->addStatusToHistory($status, $order_note, false);
        $this->saveOrder($order);
    }

    /**
     * Check is order contain downloadable product
     * @return bool
     */
    public function isContainDownloadableProduct()
    {
        $items = $this->getQuote()->getAllItems();
        foreach ($items as $item) {
            //look for downloadable products
            if ($item->getProductType() === 'downloadable') {
                return true;
                break;
            } else {
                return false;
                break;
            }
        }
    }

    /**
     * Save order with order repository
     *
     * @param OrderInterface $order
     * @throws \Exception
     */
    public function saveOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        try {
            $this->_orderRepository->save($order);
        } catch (\Exception $e) {
            $error_exception = "AbstractAction.class SaveOrder : " . $e;
            $this->_midtransLogger->midtransError($error_exception);
        }
    }

    /**
     * Validate the full refund doesn't create credit memo twice
     *
     * @param $refund_key
     * @param $order
     * @param $refund_amount
     * @return bool
     */
    public function canFullRefund($refund_key, $order, $refund_amount)
    {
        $foundedRefundKey = null;

        $commentStatusHistory = $order->getStatusHistories();
        $creditMemo = $order->getCreditmemosCollection();
        foreach ($commentStatusHistory as $value) {
            $valueComment = "Refunded " . $refund_amount . " | Refund-Key " . $value->getComment();
            if (strpos($valueComment, $refund_key) !== false) {
                $foundedRefundKey = true;
                break;
            } else {
                $foundedRefundKey = false;
            }
        }

        if ($creditMemo != null && $foundedRefundKey) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Create credit memo for refund from midtrans dashboard,
     * if refund from midtrans dashboard is fullRefund. create credit memo and cancel order.
     *
     * @param $orderId
     * @param $isFullRefund
     * @param $refund_note
     * @throws \Exception
     */
    public function createCreditMemo($orderId, $isFullRefund, $refund_note)
    {
        $order = $this->getQuoteByOrderId($orderId);
        $invoices = $order->getInvoiceCollection();
        foreach ($invoices as $invoice) {
            $invoiceIncrementId = $invoice->getIncrementId();
        }
        if (isset($invoiceIncrementId)) {
            $orderInvoice = $this->_invoice->loadByIncrementId($invoiceIncrementId);
        } else {
            $this->_midtransLogger->midtransError("AbstractAction.class CreateCreditMemo:: Failed create Credit Memo with order-id " . $orderId . " Invoice not found");
        }
        if ($isFullRefund) {
            $creditMemo = $this->_creditmemoFactory->createByOrder($order);
            if (isset($orderInvoice)) {
                $creditMemo->setInvoice($orderInvoice);
            } else {
                $this->_midtransLogger->midtransError("AbstractAction.class CreateCreditMemo:: Failed create Credit Memo with order-id " . $orderId . " Invoice not found");
            }
            $creditMemo->setState(Order\Creditmemo::STATE_REFUNDED);
            $this->cancelOrder($orderId, Order::STATE_CLOSED, $refund_note);
        }
        try {
            if (isset($creditMemo)) {
                $this->_creditmemoRepository->save($creditMemo);
            } else {
                $this->_midtransLogger->midtransError("AbstractAction.class CreateCreditMemo:: Failed create Credit Memo with order-id " . $orderId . " Invoice not found");
            }
        } catch (CouldNotSaveException $e) {
            $error_exception = "AbstractAction.class saveCreditMemo :" . $e;
            $this->_midtransLogger->midtransError($error_exception);
        }
    }
}
