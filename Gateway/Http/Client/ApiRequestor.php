<?php

namespace Midtrans\Snap\Gateway\Http\Client;

use Exception;
use Magento\Framework\HTTP\Client\Curl;
use Midtrans\Snap\Gateway\Config\Config;

/**
 * Send request to Midtrans API
 * Better don't use this class directly, use CoreApi, Transaction
 */

class ApiRequestor
{

    /**
     * Send GET request
     *
     * @param string $url
     * @param string $server_key
     * @param mixed[] $data_hash
     * @return mixed
     * @throws Exception
     */
    public static function get($url, $server_key, $data_hash)
    {
        return self::remoteCall($url, $server_key, $data_hash, "GET");
    }

    /**
     * Send POST request
     *
     * @param string $url
     * @param string $server_key
     * @param mixed[] $data_hash
     * @return mixed
     * @throws Exception
     */
    public static function post($url, $server_key, $data_hash)
    {
        return self::remoteCall($url, $server_key, $data_hash, "POST");
    }

    /**
     * Actually send request to API server
     *
     * @param string $url
     * @param string $server_key
     * @param mixed[] $data_hash
     * @param $method
     * @return mixed
     * @throws Exception
     */
    public static function remoteCall($url, $server_key, $data_hash, $method)
    {
        $curl = new Curl();
        if (!$server_key) {
            throw new Exception(
                'The ServerKey/ClientKey is null, You need to set the server-key from Config. Please double-check Config and ServerKey key. ' .
                'You can check from the Midtrans Dashboard. ' .
                'See https://docs.midtrans.com/en/midtrans-account/overview?id=retrieving-api-access-keys ' .
                'for the details.'
            );
        } else {
            if ($server_key == "") {
                throw new Exception(
                    'The ServerKey/ClientKey is invalid, as it is an empty string. Please double-check your ServerKey key. ' .
                    'You can check from the Midtrans Dashboard. ' .
                    'See https://docs.midtrans.com/en/midtrans-account/overview?id=retrieving-api-access-keys ' .
                    'for the details.'
                );
            }
        }

        $pluginVersion = Config::getMagentoPluginVersion();
        $headers = array(
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "User-Agent" => "Magento 2 Module",
            "X-Plugin-Name" => "midtrans-magento2-v$pluginVersion",
            "X-Source" => "Magento 2 plugin",
            "X-Source-Version" => "$pluginVersion"
        );
        $curl->setOption(CURLOPT_RETURNTRANSFER, true);

        // Set append notification to header
        if (Config::$appendNotifUrl) $headers['X-Append-Notification'] = Config::$appendNotifUrl;
        // Set override notification to header
        if (Config::$overrideNotifUrl) $headers['X-Override-Notification'] = Config::$overrideNotifUrl;

        $curl->setHeaders($headers);
        $curl->setCredentials($server_key, "");

        if ($method === "POST") {
            $body = json_encode($data_hash);
            $curl->post($url, $body);
        } elseif ($method === "GET") {
            $curl->get($url);
        }

        $result = $curl->getBody();

        try {
            $result_array = json_decode($result);
        } catch (Exception $e) {
            throw new Exception("API Request Error unable to json_decode API response: ".$result . ' | Request url: '.$url);
        }
        $httpCode = $curl->getStatus();
        if (isset($result_array->status_code) && $result_array->status_code >= 401 && $result_array->status_code != 407) {
            throw new Exception('Midtrans API is returning API error. HTTP status code: ' . $result_array->status_code . ' API response: ' . $result, $result_array->status_code);
        } elseif ($httpCode >= 400) {
            throw new Exception('Midtrans API is returning API error. HTTP status code: ' . $httpCode . ' API response: ' . $result, $httpCode);
        } else {
            return $result_array;
        }

    }

    private static function processStubed($curl, $url, $server_key, $data_hash, $post)
    {
        VT_Tests::$lastHttpRequest = [
             "url" => $url,
             "server_key" => $server_key,
             "data_hash" => $data_hash,
             "post" => $post,
             "curl" => $curl
        ];

        return VT_Tests::$stubHttpResponse;
    }

}
