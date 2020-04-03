<?php

namespace Midtrans\Snap\Logger;


use Midtrans\Snap\Model\Config\Source\Payment\Settings;
use Monolog\Logger;

/**
 * Class MidtransLogger handle logging request, notification and error,
 * all log file located at var/log/midtrans folder
 *
 * @package Midtrans\Snap\Logger
 */
class MidtransLogger extends Logger
{
    const REQUEST = 100;
    const ERROR = 400;
    const NOTIFICATION = 200;

    protected $settings;

    /**
     * MidtransLogger constructor.
     *
     * @param string $name
     * @param array $handlers
     * @param array $processors
     * @param Settings $settings
     */
    function __construct(
        $name , array $handlers = array(),
        array $processors = array(),
        Settings $settings
    )
    {
        parent::__construct($name, $handlers, $processors);
        $this->settings = $settings;
    }


    /**
     * Do record the notification log
     *
     * @param $message
     * @param array $context
     * @return bool|null
     */
    public function midtransNotification($message, array $context = [])
    {
        if ($this->settings->isNotificationLogEnabled()) {
            return $this->addRecord(static::NOTIFICATION, $message, $context);
        }
        return null;
    }

    /**
     * Do a record request log payload to midtrans gateway
     *
     * @param $message
     * @param array $context
     * @return bool|null
     */
    public function midtransRequest($message, array $context = [])
    {
        if ($this->settings->isRequestLogEnabled()) {
            return $this->addRecord(static::REQUEST, $message, $context);
        }
        return null;
;
    }

    /**
     * Do record a Midtrans Error log exception. If logging is disabled,
     * return throw new exception if exception is enabled.
     *
     * @param $message
     * @param array $context
     * @return bool
     * @throws \Exception
     */
    public function midtransError($message, array $context = [])
    {
        if ($this->settings->isErrorLogEnabled()) {
            return $this->addRecord(static::ERROR, $message, $context);
        } elseif ($this->settings->isExceptionEnabled()) {
            throw new \Exception($message, self::ERROR);
        } else {
            return null;
        }
    }

    /**
     * Global function to record log
     *
     * @param int $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addRecord($level, $message, array $context = array())
    {
        $context['is_exception'] = $message instanceof \Exception;
        return parent::addRecord($level, $message, $context);
    }
}
