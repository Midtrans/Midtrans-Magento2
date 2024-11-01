<?php

namespace Midtrans\Snap\Controller\Payment;

use Exception;
use Magento\Customer\Model\Context;
use Midtrans\Snap\Gateway\Config\Config;
use Midtrans\Snap\Gateway\SnapApi;

class Multishipping extends Action
{
    public function execute()
    {
        $isLoggedIn = $this->_contextHttp->getValue(Context::CONTEXT_AUTH);
        if ($isLoggedIn) {
            $multishipping = [];
            try {
                // 1. Get checkout session
                $checkoutSession = $this->_checkoutSession->getData();
                if ($checkoutSession['checkout_state'] === 'multishipping_success' || isset($checkoutSession['last_quote_id'])) {
                    // 2. Get quote-id from checkout session
                    $quoteId = $checkoutSession['last_quote_id'];

                    $paymentCode = $this->paymentOrderRepository->getPaymentCodeByQuoteId($quoteId);

                    $requestConfig = $this->getMidtransDataConfig()->getRequestConfig($paymentCode);
                    $isRedirect = $this->getMidtransDataConfig()->isRedirect();
                    $is3ds = $requestConfig['is3ds'];
                    $isProduction = $this->getMidtransDataConfig()->isProduction();
                    $serverKey =$this->getMidtransDataConfig()->getServerKey($paymentCode);

                    $multishipping['quote_id'] = $quoteId;
                    Config::$isProduction = $isProduction;
                    Config::$serverKey = $serverKey;
                    Config::$isSanitized = true;
                    Config::$is3ds = $is3ds;

                    $payloads = $this->paymentRequestRepository->getPayload($requestConfig, $paymentCode, null, $multishipping);

                    /*Override notification, if override notification from admin setting is active (default is active) */
                    if ($this->getMidtransDataConfig()->isOverrideNotification() && $this->getMidtransDataConfig()->getNotificationEndpoint() != null) {
                        Config::$overrideNotifUrl = $this->getMidtransDataConfig()->getNotificationEndpoint();
                    }

                    $_info = 'Info - Payloads: ' . print_r($payloads, true);
                    $this->_midtransLogger->midtransRequest($_info);

                    $paymentOrderId = $payloads['transaction_details']['order_id'];
                    $this->setValue($paymentOrderId);

                    $snapApi = new SnapApi();
                    $data = null;


                    if (!$isRedirect) {
                        $token = $snapApi::getSnapToken($payloads);
                        $data = $token;
                        $_info = 'Info-Multishipping-Snap token: ' . print_r($data, true);
                        $this->_midtransLogger->midtransRequest($_info);

                        $firstName = $payloads['customer_details']['billing_address']['first_name'];
                        $lastName = $payloads['customer_details']['billing_address']['last_name'];
                        $address = $payloads['customer_details']['billing_address']['address'];
                        $postalCode = $payloads['customer_details']['billing_address']['postal_code'];
                        $countryCode = $payloads['customer_details']['billing_address']['country_code'];
                        $phone = $payloads['customer_details']['billing_address']['phone'];
                        $email = $payloads['customer_details']['email'];

                        $amount = $payloads['transaction_details']['gross_amount'];

                        $this->registry->register('token', $data, false);
                        $this->registry->register('amount', $amount, false);
                        $this->registry->register('full_name', $firstName . ' ' . $lastName, false);
                        $this->registry->register('address', $address, false);
                        $this->registry->register('postal_code', $postalCode, false);
                        $this->registry->register('country_code', $countryCode, false);
                        $this->registry->register('phone', $phone, false);
                        $this->registry->register('email', $email, false);

                        $this->registry->register('payment_code', $paymentCode, false);
                    } else {
                        $redirect_url = $snapApi::createTransaction($payloads)->redirect_url;
                        $data = $redirect_url;
                        $_info = 'Info-Multishipping-Redirect URL :' . print_r($data, true);
                        $this->_midtransLogger->midtransRequest($_info);
                        $this->_redirect($redirect_url);
                    }
                } else {
                    // session checkout not found redirect to cart
                    return $this->resultRedirectFactory->create()->setPath('checkout/cart');
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                $this->_midtransLogger->midtransError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }
        } else {
            // If not logged in redirect to Home page
            return $this->resultRedirectFactory->create()->setPath('/');
        }
        return $this->_pageFactory->create();
    }
}
