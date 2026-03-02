<?php

declare(strict_types=1);

use App\Enums\VehicleType;

it('has correct values', function (): void {
    expect(VehicleType::Bicycle->value)->toBe('bicycle')
        ->and(VehicleType::Motorcycle->value)->toBe('motorcycle')
        ->and(VehicleType::Car->value)->toBe('car');
});

it('returns correct labels', function (): void {
    expect(VehicleType::Bicycle->label())->toBe('Bicycle')
        ->and(VehicleType::Motorcycle->label())->toBe('Motorcycle')
        ->and(VehicleType::Car->label())->toBe('Car');
});
