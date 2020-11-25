<?php

namespace Midtrans\Snap\Gateway;

use Midtrans\Snap\Gateway\Config\Config;
use Midtrans\Snap\Gateway\Http\Client\ApiRequestor;
use Midtrans\Snap\Gateway\Utility\Sanitizer;

/**
 * Provide charge and capture functions for Core API
 */
class CoreApi
{
    /**
     * Create transaction.
     *
     * @param mixed[] $params Transaction options
     * @return mixed
     * @throws \Exception
     */
    public static function charge($params)
    {
        $payloads = [
            'payment_type' => 'credit_card'
        ];

        if (isset($params['item_details'])) {
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

        if (Config::$appendNotifUrl) {
            Config::$curlOptions[CURLOPT_HTTPHEADER][] = 'X-Append-Notification: ' . Config::$appendNotifUrl;
        }

        if (Config::$overrideNotifUrl) {
            Config::$curlOptions[CURLOPT_HTTPHEADER][] = 'X-Override-Notification: ' . Config::$overrideNotifUrl;
        }

        return ApiRequestor::post(
            Config::getBaseUrl() . '/charge',
            Config::$serverKey,
            $payloads
        );
    }

    /**
     * Capture pre-authorized transaction
     *
     * @param string $param Order ID or transaction ID, that you want to capture
     * @return mixed
     * @throws \Exception
     */
    public static function capture($param)
    {
        $payloads = [
        'transaction_id' => $param,
        ];

        return ApiRequestor::post(
            Config::getBaseUrl() . '/capture',
            Config::$serverKey,
            $payloads
        );
    }
}
