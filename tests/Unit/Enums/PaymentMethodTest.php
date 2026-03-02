<?php

declare(strict_types=1);

use App\Enums\PaymentMethod;

it('has correct values', function (): void {
    expect(PaymentMethod::CashOnDelivery->value)->toBe('cash_on_delivery')
        ->and(PaymentMethod::Polar->value)->toBe('polar');
});

it('returns correct labels', function (): void {
    expect(PaymentMethod::CashOnDelivery->label())->toBe('Cash on Delivery')
        ->and(PaymentMethod::Polar->label())->toBe('Polar');
});
