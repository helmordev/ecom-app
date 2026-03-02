<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderType: string
{
    case FoodDelivery = 'food_delivery';
    case ProductOrder = 'product_order';

    public function label(): string
    {
        return match ($this) {
            OrderType::FoodDelivery => 'Food Delivery',
            OrderType::ProductOrder => 'Product Order',
        };
    }
}
