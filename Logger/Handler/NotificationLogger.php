<?php

namespace Midtrans\Snap\Logger\Handler;

use Midtrans\Snap\Logger\MidtransLogger;

/**
 * Class NotificationLogger for specify the file name, logger type and level
 */
class NotificationLogger extends BaseLogger
{
    /**
     * path of notification log file
     * @var string
     */
    protected $fileName = '/var/log/midtrans/notification.log';

    /**
     * Code for logger type, the value is 200
     * @var int
     */
    protected $loggerType = MidtransLogger::NOTIFICATION;

    /**
     * Level for logger, the value is 200
     * @var int
     */
    protected $level = MidtransLogger::NOTIFICATION;
}
