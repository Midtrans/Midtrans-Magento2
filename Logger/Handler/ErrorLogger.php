<?php

namespace Midtrans\Snap\Logger\Handler;

use Midtrans\Snap\Logger\MidtransLogger;

/**
 * Class ErrorLogger for specify the file name, logger type and level
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
