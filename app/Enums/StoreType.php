<?php

declare(strict_types=1);

namespace App\Enums;

enum StoreType: string
{
    case Restaurant = 'restaurant';
    case Shop = 'shop';

    public function label(): string
    {
        return match ($this) {
            StoreType::Restaurant => 'Restaurant',
            StoreType::Shop => 'Shop',
        };
    }
}
