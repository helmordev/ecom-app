<?php

declare(strict_types=1);

use App\Enums\UserRole;

it('has correct values', function (): void {
    expect(UserRole::Admin->value)->toBe('admin')
        ->and(UserRole::User->value)->toBe('user')
        ->and(UserRole::Seller->value)->toBe('seller')
        ->and(UserRole::Driver->value)->toBe('driver');
});

it('returns correct labels', function (): void {
    expect(UserRole::Admin->label())->toBe('Admin')
        ->and(UserRole::User->label())->toBe('Customer')
        ->and(UserRole::Seller->label())->toBe('Seller')
        ->and(UserRole::Driver->label())->toBe('Driver');
});

it('can be created from value', function (): void {
    expect(UserRole::from('admin'))->toBe(UserRole::Admin)
        ->and(UserRole::from('seller'))->toBe(UserRole::Seller);
});
