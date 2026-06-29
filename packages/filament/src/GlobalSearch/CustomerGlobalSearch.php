<?php

namespace Lunar\Filament\GlobalSearch;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Customer;

/**
 * @extends GlobalSearchDescriptor<Customer>
 */
class CustomerGlobalSearch extends GlobalSearchDescriptor
{
    public static function getModelContract(): string
    {
        return Customer::class;
    }

    public static function getSearchableAttributes(): array
    {
        return [
            'first_name',
            'last_name',
            'company_name',
            'account_ref',
            'tax_identifier',
            'users.name',
            'users.email',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'users',
        ]);
    }

    public static function getResultTitle(Model $record): string|Htmlable
    {
        /** @var Customer $record */
        return (string) ($record->company_name ?: $record->fullName);
    }

    public static function getResultDetails(Model $record): array
    {
        /** @var Customer $record */
        $details = [
            __('lunar-filament::global-search.customers.details.full_name') => $record->fullName,
            __('lunar-filament::global-search.customers.details.email') => $record->users->first()?->email,
            __('lunar-filament::global-search.customers.details.company') => $record->company_name,
            __('lunar-filament::global-search.customers.details.account_ref') => $record->account_ref,
        ];

        return array_filter($details, fn ($value) => filled($value));
    }
}
