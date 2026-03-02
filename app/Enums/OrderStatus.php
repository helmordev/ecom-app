<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case ReadyForPickup = 'ready_for_pickup';
    case Shipped = 'shipped';
    case Assigned = 'assigned';
    case PickedUp = 'picked_up';
    case OnTheWay = 'on_the_way';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            OrderStatus::Pending => 'Pending',
            OrderStatus::Confirmed => 'Confirmed',
            OrderStatus::Processing => 'Processing',
            OrderStatus::ReadyForPickup => 'Ready for Pickup',
            OrderStatus::Shipped => 'Shipped',
            OrderStatus::Assigned => 'Assigned',
            OrderStatus::PickedUp => 'Picked Up',
            OrderStatus::OnTheWay => 'On the Way',
            OrderStatus::Delivered => 'Delivered',
            OrderStatus::Cancelled => 'Cancelled',
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            OrderStatus::Delivered, OrderStatus::Cancelled => true,
            default => false,
        };
    }
}
