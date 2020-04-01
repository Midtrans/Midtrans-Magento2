<?php
/**
 *
 *        c.                                            c.  :.
 *        E1                                            E.  !)
 * ::.    E1    !3.        ,5"`'\.;F'`"t.   i.   cF'`'=.E.  !7''`   ;7""   '""!.    ;7"'"!.   ;7'`'*=
 * ::.    E1    !3.        t.    !)     t   i.  tL      t.  !)     !)     ,,...;)  :1     I.  !t.,
 * ::.    E1    !3.        t.    !)     t   i.  E.      E.  !)     !L    t'    :1  :1     !)    ``"1.
 * ::.    E1    !3.        t.    !)     t   i.  '1.,  ,ct.  !1,    !L    1.  ,;31  :1     !) -..   ;7
 * '      E1    `'         `            `   `     ``'`  `    `'``  `      `''`  `   `     `    ``'`
 *        E7
 *
 * Midtrans Snap Magento 2 Module
 *
 * Copyright (c) 2020 Midtrans PT.
 * This file is open source and available under the MIT license.
 * See the LICENSE file for more info.
 *
 */
namespace Midtrans\Snap\Gateway;

use Midtrans\Snap\Gateway\Http\Client\ApiRequestor;
use Midtrans\Snap\Gateway\Config\Config;
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
     */
    public static function status($id)
    {
        return ApiRequestor::get(
            Config::getBaseUrl() . '/' . $id . '/status',
            Config::$serverKey,
            false
        );
    }

    /**
     * Approve challenge transaction
     *
     * @param string $id Order ID or transaction ID
     *
     * @return string
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
     * @return mixed[]
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
     * @param string $id Order ID or transaction ID
     *
     * @return mixed[]
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
