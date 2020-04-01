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

use Midtrans\Snap\Gateway\Config\Config;
use Midtrans\Snap\Gateway\Utility\Sanitizer;
use Midtrans\Snap\Gateway\Http\Client\ApiRequestor;
/**
 * Provide charge and capture functions for Core API
 */
class CoreApi
{
    /**
     * Create transaction.
     *
     * @param mixed[] $params Transaction options
     */
    public static function charge($params)
    {
        $payloads = array(
            'payment_type' => 'credit_card'
        );

        if (array_key_exists('item_details', $params)) {
            $gross_amount = 0;
            foreach ($params['item_details'] as $item) {
                $gross_amount += $item['quantity'] * $item['price'];
            }
            $payloads['transaction_details']['gross_amount'] = $gross_amount;
        }

        $payloads = array_replace_recursive($payloads, $params);

        if (Config::$isSanitized) {
            Sanitizer::jsonRequest($payloads);
        }

        $result = ApiRequestor::post(
            Config::getBaseUrl() . '/charge',
            Config::$serverKey,
            $payloads
        );

        return $result;
    }

    /**
     * Capture pre-authorized transaction
     *
     * @param string $param Order ID or transaction ID, that you want to capture
     */
    public static function capture($param)
    {
        $payloads = array(
        'transaction_id' => $param,
        );

        $result = ApiRequestor::post(
            Config::getBaseUrl() . '/capture',
            Config::$serverKey,
            $payloads
        );

        return $result;
    }
}
