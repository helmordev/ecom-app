<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case CashOnDelivery = 'cash_on_delivery';
    case Polar = 'polar';

    public function label(): string
    {
        return match ($this) {
            PaymentMethod::CashOnDelivery => 'Cash on Delivery',
            PaymentMethod::Polar => 'Polar',
        };
    }
}
