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

/**
 * Read raw post input and parse as JSON. Provide getters for fields in notification object
 *
 * Example:
 *
 * ```php
 *
 *   namespace Midtrans;
 *
 *   $notif = new Notification();
 *   echo $notif->order_id;
 *   echo $notif->transaction_status;
 * ```
 */
class Notification
{
    private $response;

    public function __construct($input_source = "php://input")
    {
        $raw_notification = json_decode(file_get_contents($input_source), true);
        $status_response = Transaction::status($raw_notification['transaction_id']);
        $this->response = $status_response;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->response)) {
            return $this->response->$name;
        }
    }
}
