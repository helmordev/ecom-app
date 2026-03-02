<?php

declare(strict_types=1);

use App\Enums\StoreType;

it('has correct values', function (): void {
    expect(StoreType::Restaurant->value)->toBe('restaurant')
        ->and(StoreType::Shop->value)->toBe('shop');
});

it('returns correct labels', function (): void {
    expect(StoreType::Restaurant->label())->toBe('Restaurant')
        ->and(StoreType::Shop->label())->toBe('Shop');
});
