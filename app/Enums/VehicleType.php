<?php

declare(strict_types=1);

namespace App\Enums;

enum VehicleType: string
{
    case Bicycle = 'bicycle';
    case Motorcycle = 'motorcycle';
    case Car = 'car';

    public function label(): string
    {
        return match ($this) {
            VehicleType::Bicycle => 'Bicycle',
            VehicleType::Motorcycle => 'Motorcycle',
            VehicleType::Car => 'Car',
        };
    }
}
