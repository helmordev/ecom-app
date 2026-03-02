<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            PaymentStatus::Pending => 'Pending',
            PaymentStatus::Paid => 'Paid',
            PaymentStatus::Failed => 'Failed',
            PaymentStatus::Refunded => 'Refunded',
        };
    }
}
