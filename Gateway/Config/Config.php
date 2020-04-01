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
namespace Midtrans\Snap\Gateway\Config;

/**
 * Midtrans Configuration
 */
class Config
{
    /**
     * Your merchant's server key
     *
     * @static
     */
    public static $serverKey;
    /**
     * Your merchant's client key
     *
     * @static
     */
    public static $clientKey;
    /**
     * True for production
     * false for sandbox mode
     *
     * @static
     */
    public static $isProduction = false;
    /**
     * Set it true to enable 3D Secure by default
     *
     * @static
     */
    public static $is3ds = false;
    /**
     * Enable request params sanitizer (validate and modify charge request params).
     * See Midtrans_Sanitizer for more details
     *
     * @static
     */
    public static $isSanitized = false;
    /**
     * Default options for every request
     *
     * @static
     */
    public static $curlOptions = array();

    const SANDBOX_BASE_URL = 'https://api.sandbox.midtrans.com/v2';
    const PRODUCTION_BASE_URL = 'https://api.midtrans.com/v2';
    const SNAP_SANDBOX_BASE_URL = 'https://app.sandbox.midtrans.com/snap/v1';
    const SNAP_PRODUCTION_BASE_URL = 'https://app.midtrans.com/snap/v1';

    /**
     * Get baseUrl
     *
     * @return string Midtrans API URL, depends on $isProduction
     */
    public static function getBaseUrl()
    {
        return Config::$isProduction ?
        Config::PRODUCTION_BASE_URL : Config::SANDBOX_BASE_URL;
    }

    /**
     * Get snapBaseUrl
     *
     * @return string Snap API URL, depends on $isProduction
     */
    public static function getSnapBaseUrl()
    {
        return Config::$isProduction ?
        Config::SNAP_PRODUCTION_BASE_URL : Config::SNAP_SANDBOX_BASE_URL;
    }
}
