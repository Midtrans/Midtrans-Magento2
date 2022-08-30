<?php

namespace Midtrans\Snap\Logger\Handler;

use Midtrans\Snap\Logger\MidtransLogger;
use Magento\Framework\Logger\Handler\Base;


/**
 * Class NotificationLogger for specify the file name, logger type and level
 */
class NotificationLogger extends Base
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
