<?php

namespace Midtrans\Snap\Logger\Handler;

use Midtrans\Snap\Logger\MidtransLogger;

/**
 * Class RequestLogger for specify the file name, logger type and level
 */
class RequestLogger extends BaseLogger
{
    /**
     * path of request log file
     * @var string
     */
    protected $fileName = '/var/log/midtrans/request.log';

    /**
     * Code for logger type, the value is 100
     * @var int
     */
    protected $loggerType = MidtransLogger::REQUEST;

    /**
     * Level for logger, the value is 100
     * @var int
     */
    protected $level = MidtransLogger::REQUEST;
}
