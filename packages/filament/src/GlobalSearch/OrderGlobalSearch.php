<?php

namespace Lunar\Filament\GlobalSearch;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

/**
 * @extends GlobalSearchDescriptor<Order>
 */
class OrderGlobalSearch extends GlobalSearchDescriptor
{
    public static function getModelContract(): string
    {
        return OrderContract::class;
    }

    public static function getSearchableAttributes(): array
    {
        return [
            'reference',
            'customer_reference',
            'notes',
            'billingAddress.first_name',
            'billingAddress.last_name',
            'billingAddress.contact_email',
            'tags.value',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'billingAddress',
            'tags',
        ]);
    }

    public static function getResultTitle(Model $record): string|Htmlable
    {
        /** @var Order $record */
        return (string) $record->reference;
    }

    public static function getResultDetails(Model $record): array
    {
        /** @var Order $record */
        $details = [
            __('lunar-filament::global-search.orders.details.status') => __('lunar::states.order.'.$record->lifecycleStatus()),
            __('lunar-filament::global-search.orders.details.total') => $record->format('total'),
            __('lunar-filament::global-search.orders.details.customer') => $record->billingAddress?->fullName,
        ];

        if ($record->billingAddress?->contact_email) {
            $details[__('lunar-filament::global-search.orders.details.email')] = $record->billingAddress->contact_email;
        }

        if ($record->placed_at) {
            $details[__('lunar-filament::global-search.orders.details.date')] = $record->placed_at;
        }

        return $details;
    }
}
