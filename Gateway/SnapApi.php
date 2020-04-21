<?php

namespace Midtrans\Snap\Gateway;

use Midtrans\Snap\Gateway\Config\Config;
use Midtrans\Snap\Gateway\Utility\Sanitizer;
use Midtrans\Snap\Gateway\Http\Client\SnapApiRequestor;
/**
 * Create Snap payment page and return snap token
 */
class SnapApi

{
    /**
     * Create Snap payment page
     *
     * Example:
     *
     * ```php
     *
     *   namespace Midtrans;
     *
     *   $params = array(
     *     'transaction_details' => array(
     *       'order_id' => rand(),
     *       'gross_amount' => 10000,
     *     )
     *   );
     *   $paymentUrl = Snap::getSnapToken($params);
     * ```
     *
     * @param  array $params Payment options
     * @return string Snap token.
     * @throws Exception curl error or midtrans error
     */
    public static function getSnapToken($params)
    {
        return (SnapApi::createTransaction($params)->token);
    }

    /**
     * Create Snap payment page, with this version returning full API response
     *
     * Example:
     *
     * ```php
     *   $params = array(
     *     'transaction_details' => array(
     *       'order_id' => rand(),
     *       'gross_amount' => 10000,
     *     )
     *   );
     *   $paymentUrl = Snap::getSnapToken($params);
     * ```
     *
     * @param  array $params Payment options
     * @return object Snap response (token and redirect_url).
     * @throws Exception curl error or midtrans error
     */
    public static function createTransaction($params)
    {
        $payloads = array(
        'credit_card' => array(
            'secure' => Config::$is3ds
        )
        );

        if (array_key_exists('item_details', $params)) {
            $gross_amount = 0;
            foreach ($params['item_details'] as $item) {
                $gross_amount += $item['quantity'] * $item['price'];
            }
            $params['transaction_details']['gross_amount'] = $gross_amount;
        }

        if (Config::$isSanitized) {
            Sanitizer::jsonRequest($params);
        }

        $params = array_replace_recursive($payloads, $params);

        $result = SnapApiRequestor::post(
            Config::getSnapBaseUrl() . '/transactions',
            Config::$serverKey,
            $params
        );

        return $result;
    }
}
