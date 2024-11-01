<?php

namespace Midtrans\Snap\Logger;

use Exception;
use Midtrans\Snap\Model\Config\Source\Payment\Settings;
use Monolog\Logger;

/**
 * Class MidtransLogger handle logging request, notification and error,
 * all log file located at var/log/midtrans folder
 *
 */
class MidtransLogger extends Logger
{
    const REQUEST = 100;
    const ERROR = 400;
    const NOTIFICATION = 200;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * MidtransLogger constructor.
     * @param string $name
     * @param Settings $settings
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(string $name, Settings $settings, array $handlers = [], array $processors = [])
    {
        $this->settings = $settings;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Do record the notification log
     *
     * @param string $message
     * @param array $context
     * @return bool|null
     */
    public function midtransNotification(string $message, array $context = [])
    {
        if ($this->settings->isNotificationLogEnabled()) {
            return $this->addRecord(static::NOTIFICATION, $message, $context);
        }
        return null;
    }

    /**
     * Do a record request log payload to midtrans gateway
     *
     * @param string $message
     * @param array $context
     * @return bool|null
     */
    public function midtransRequest(string $message, array $context = [])
    {
        if ($this->settings->isRequestLogEnabled()) {
            return $this->addRecord(static::REQUEST, $message, $context);
        }
        return null;
    }

    /**
     * Do record a Midtrans Error log exception. If logging is disabled,
     * return throw new exception if exception is enabled.
     * @param string $message
     * @param array $context
     * @return bool
     * @throws Exception
     */
    public function midtransError(string $message, array $context = [])
    {
        if ($this->settings->isErrorLogEnabled()) {
            return $this->addRecord(static::ERROR, $message, $context);
        } elseif ($this->settings->isExceptionEnabled()) {
            throw new Exception($message, self::ERROR);
        } else {
            return null;
        }
    }
}
