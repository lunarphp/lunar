<?php

namespace Lunar\Tests\Panel\Fixtures\Customers\Tables;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Panel\Tables\TableFilter;

class CompanyFilter extends TableFilter
{
    public function key(): string
    {
        return 'has_company';
    }

    public function label(): string
    {
        return 'Company';
    }

    /** @return array<string, string> */
    public function options(): array
    {
        return [
            'yes' => 'Has company',
            'no' => 'No company',
        ];
    }

    public function query(Builder $query, mixed $value): void
    {
        $value === 'yes'
            ? $query->whereNotNull('company_name')->where('company_name', '!=', '')
            : $query->where(fn (Builder $inner) => $inner->whereNull('company_name')->orWhere('company_name', ''));
    }
}
