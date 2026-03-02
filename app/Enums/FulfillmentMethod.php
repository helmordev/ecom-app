<?php

declare(strict_types=1);

namespace App\Enums;

enum FulfillmentMethod: string
{
    case PlatformDelivery = 'platform_delivery';
    case SellerShipping = 'seller_shipping';

    public function label(): string
    {
        return match ($this) {
            FulfillmentMethod::PlatformDelivery => 'Platform Delivery',
            FulfillmentMethod::SellerShipping => 'Seller Shipping',
        };
    }
}
