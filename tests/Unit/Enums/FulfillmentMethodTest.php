<?php

declare(strict_types=1);

use App\Enums\FulfillmentMethod;

it('has correct values', function (): void {
    expect(FulfillmentMethod::PlatformDelivery->value)->toBe('platform_delivery')
        ->and(FulfillmentMethod::SellerShipping->value)->toBe('seller_shipping');
});

it('returns correct labels', function (): void {
    expect(FulfillmentMethod::PlatformDelivery->label())->toBe('Platform Delivery')
        ->and(FulfillmentMethod::SellerShipping->label())->toBe('Seller Shipping');
});
