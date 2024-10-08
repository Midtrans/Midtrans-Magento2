<?php

namespace Midtrans\Snap\Gateway;

use Midtrans\Snap\Gateway\Http\Client\ApiRequestor;
use Midtrans\Snap\Gateway\Config\Config;
use Midtrans\Snap\Gateway\Utility\PaymentUtils;

/**
 * API methods to get transaction status, approve and cancel transactions
 */
class Transaction
{

    /**
     * Retrieve transaction status
     *
     * @param string $id Order ID or transaction ID
     *
     * @return mixed[]
     * @throws \Exception
     */
    public static function status($id, $paymentType = null)
    {
        return ApiRequestor::get(
            Config::getBaseUrl() . '/' . $id . '/status',
            Config::$serverKey,
            false,
            PaymentUtils::isOpenApi($paymentType)
        );
    }

    /**
     * Approve challenge transaction
     *
     * @param string $id Order ID or transaction ID
     *
     * @return string
     * @throws \Exception
     */
    public static function approve($id)
    {
        return ApiRequestor::post(
            Config::getBaseUrl() . '/' . $id . '/approve',
            Config::$serverKey,
            false
        )->status_code;
    }

    /**
     * Cancel transaction before it's settled
     *
     * @param string $id Order ID or transaction ID
     *
     * @return string
     * @throws \Exception
     */
    public static function cancel($id)
    {
        return ApiRequestor::post(
            Config::getBaseUrl() . '/' . $id . '/cancel',
            Config::$serverKey,
            false
        )->status_code;
    }

    /**
     * Expire transaction before it's setteled
     *
     * @param string $id Order ID or transaction ID
     *
     * @return mixed[]
     * @throws \Exception
     */
    public static function expire($id)
    {
        return ApiRequestor::post(
            Config::getBaseUrl() . '/' . $id . '/expire',
            Config::$serverKey,
            false
        );
    }

    /**
     * Transaction status can be updated into refund
     * if the customer decides to cancel completed/settlement payment.
     * The same refund id cannot be reused again.
     *
     * @param string $id Order ID or transaction ID
     *
     * @param $params
     * @return mixed[]
     * @throws \Exception
     */
    public static function refund($id, $params)
    {
        return ApiRequestor::post(
            Config::getBaseUrl() . '/' . $id . '/refund',
            Config::$serverKey,
            $params
        );
    }
    /**
     * Transaction status can be updated into refund
     * if the customer decides to cancel completed/settlement payment.
     * The same refund id cannot be reused again.
     *
     * @param string $id is transaction ID for SnapBI
     *
     * @param $params
     * @return mixed[]
     * @throws \Exception
     */
    public static function refundWithSnapBi($id, $params)
    {
        return ApiRequestor::post(
            Config::getBaseUrl() . '/' . $id . '/refund',
            Config::$serverKey,
            $params,
            true
        );
    }
    /**
     * Transaction status can be updated into refund
     * if the customer decides to cancel completed/settlement payment.
     * The same refund id cannot be reused again.
     *
     * @param string $id Order ID or transaction ID
     *
     * @param $params
     * @return mixed[]
     * @throws \Exception
     */
    public static function refundDirect($id, $params)
    {
        return ApiRequestor::post(
            Config::getBaseUrl() . '/' . $id . '/refund/online/direct',
            Config::$serverKey,
            $params
        );
    }

    /**
     * Deny method can be triggered to immediately deny card payment transaction
     * in which fraud_status is challenge.
     *
     * @param string $id Order ID or transaction ID
     *
     * @return mixed[]
     * @throws \Exception
     */
    public static function deny($id)
    {
        return ApiRequestor::post(
            Config::getBaseUrl() . '/' . $id . '/deny',
            Config::$serverKey,
            false
        );
    }
}
