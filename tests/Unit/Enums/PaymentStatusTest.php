<?php

declare(strict_types=1);

use App\Enums\PaymentStatus;

it('has correct values', function (): void {
    expect(PaymentStatus::Pending->value)->toBe('pending')
        ->and(PaymentStatus::Paid->value)->toBe('paid')
        ->and(PaymentStatus::Failed->value)->toBe('failed')
        ->and(PaymentStatus::Refunded->value)->toBe('refunded');
});

it('returns correct labels', function (): void {
    expect(PaymentStatus::Pending->label())->toBe('Pending')
        ->and(PaymentStatus::Paid->label())->toBe('Paid')
        ->and(PaymentStatus::Failed->label())->toBe('Failed')
        ->and(PaymentStatus::Refunded->label())->toBe('Refunded');
});
