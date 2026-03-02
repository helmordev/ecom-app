<?php

declare(strict_types=1);

use App\Enums\OrderStatus;

it('has correct values', function (): void {
    expect(OrderStatus::Pending->value)->toBe('pending')
        ->and(OrderStatus::Confirmed->value)->toBe('confirmed')
        ->and(OrderStatus::Processing->value)->toBe('processing')
        ->and(OrderStatus::ReadyForPickup->value)->toBe('ready_for_pickup')
        ->and(OrderStatus::Shipped->value)->toBe('shipped')
        ->and(OrderStatus::Assigned->value)->toBe('assigned')
        ->and(OrderStatus::PickedUp->value)->toBe('picked_up')
        ->and(OrderStatus::OnTheWay->value)->toBe('on_the_way')
        ->and(OrderStatus::Delivered->value)->toBe('delivered')
        ->and(OrderStatus::Cancelled->value)->toBe('cancelled');
});

it('returns correct labels', function (): void {
    expect(OrderStatus::Pending->label())->toBe('Pending')
        ->and(OrderStatus::Confirmed->label())->toBe('Confirmed')
        ->and(OrderStatus::Processing->label())->toBe('Processing')
        ->and(OrderStatus::ReadyForPickup->label())->toBe('Ready for Pickup')
        ->and(OrderStatus::Shipped->label())->toBe('Shipped')
        ->and(OrderStatus::Assigned->label())->toBe('Assigned')
        ->and(OrderStatus::PickedUp->label())->toBe('Picked Up')
        ->and(OrderStatus::OnTheWay->label())->toBe('On the Way')
        ->and(OrderStatus::Delivered->label())->toBe('Delivered')
        ->and(OrderStatus::Cancelled->label())->toBe('Cancelled');
});

it('correctly identifies terminal statuses', function (): void {
    expect(OrderStatus::Delivered->isTerminal())->toBeTrue()
        ->and(OrderStatus::Cancelled->isTerminal())->toBeTrue()
        ->and(OrderStatus::Pending->isTerminal())->toBeFalse()
        ->and(OrderStatus::Processing->isTerminal())->toBeFalse()
        ->and(OrderStatus::Shipped->isTerminal())->toBeFalse();
});

it('has exactly 10 cases', function (): void {
    expect(OrderStatus::cases())->toHaveCount(10);
});
