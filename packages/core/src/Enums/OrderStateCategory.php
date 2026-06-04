<?php

namespace Lunar\Core\Enums;

/**
 * Groups the headline `OrderState` set into coarse buckets for admin
 * colour-coding and category-level filtering.
 *
 * Each `OrderState` declares the category it belongs to via `category()`.
 */
enum OrderStateCategory: string
{
    case Unpaid = 'unpaid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Returned = 'returned';

    public function label(): string
    {
        return __('lunar::enums.order-state-category.'.$this->value);
    }

    /**
     * Filament-compatible badge colour name for the category.
     */
    public function color(): string
    {
        return match ($this) {
            self::Unpaid => 'gray',
            self::Processing => 'warning',
            self::Shipped => 'info',
            self::Completed => 'success',
            self::Cancelled => 'danger',
            self::Returned => 'danger',
        };
    }
}
