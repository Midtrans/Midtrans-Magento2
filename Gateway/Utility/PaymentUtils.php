<?php

namespace Midtrans\Snap\Gateway\Utility;

class PaymentUtils
{
    public static function isOpenApi($paymentType): bool{
        return (strtolower($paymentType) == "dana");
    }
}
