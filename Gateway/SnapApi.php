<?php

namespace Midtrans\Snap\Gateway;

use Midtrans\Snap\Gateway\Config\Config;
use Midtrans\Snap\Gateway\Http\Client\SnapApiRequestor;
use Midtrans\Snap\Gateway\Utility\Sanitizer;

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
     * @throws Exception|\Exception curl error or midtrans error
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
     * @throws Exception|\Exception curl error or midtrans error
     */
    public static function createTransaction($params)
    {
        $payloads = [
        'credit_card' => [
            'secure' => Config::$is3ds
        ]
        ];

        if (isset($params['item_details'])) {
            $gross_amount = 0;
            foreach ($params['item_details'] as $item) {
                $gross_amount += $item['quantity'] * $item['price'];
            }
            $params['transaction_details']['gross_amount'] = $gross_amount;
        }

        if (Config::$isSanitized) {
            Sanitizer::jsonRequest($params);
        }

        if (Config::$appendNotifUrl) {
            Config::$curlOptions[CURLOPT_HTTPHEADER][] = 'X-Append-Notification: ' . Config::$appendNotifUrl;
        }

        if (Config::$overrideNotifUrl) {
            Config::$curlOptions[CURLOPT_HTTPHEADER][] = 'X-Override-Notification: ' . Config::$overrideNotifUrl;
        }

        $params = array_replace_recursive($payloads, $params);

        return SnapApiRequestor::post(
            Config::getSnapBaseUrl() . '/transactions',
            Config::$serverKey,
            $params
        );
    }
}
