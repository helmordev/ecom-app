<?php

declare(strict_types=1);

use App\Enums\OrderType;

it('has correct values', function (): void {
    expect(OrderType::FoodDelivery->value)->toBe('food_delivery')
        ->and(OrderType::ProductOrder->value)->toBe('product_order');
});

it('returns correct labels', function (): void {
    expect(OrderType::FoodDelivery->label())->toBe('Food Delivery')
        ->and(OrderType::ProductOrder->label())->toBe('Product Order');
});
