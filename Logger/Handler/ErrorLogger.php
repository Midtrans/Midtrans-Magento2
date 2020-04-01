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

namespace Midtrans\Snap\Logger\Handler;

use Midtrans\Snap\Logger\MidtransLogger;

/**
 * Class ErrorLogger for specify the file name, logger type and level
 * @package Midtrans\Snap\Logger\Handler
 */
class ErrorLogger extends BaseLogger
{
    /**
     * path of Error log file
     * @var string
     */
    protected $fileName = '/var/log/midtrans/error.log';

    /**
     * Code for logger type, the value is 500
     * @var int
     */
    protected $loggerType = MidtransLogger::ERROR;

    /**
     * Level for logger, the value is 500
     * @var int
     */
    protected $level = MidtransLogger::ERROR;

}
