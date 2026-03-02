<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';
    case Seller = 'seller';
    case Driver = 'driver';

    public function label(): string
    {
        return match ($this) {
            UserRole::Admin => 'Admin',
            UserRole::User => 'Customer',
            UserRole::Seller => 'Seller',
            UserRole::Driver => 'Driver',
        };
    }
}
