<?php

namespace Midtrans\Snap\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;

class BaseLogger extends Base
{
    public function isHandling(array $record)
    {
        return $record['level'] == $this->level;
    }
}
