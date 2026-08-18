<?php

namespace LunarPanelExample\Tables;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Panel\Tables\TableFilter;

/**
 * Example TableFilter: a dropdown on the customers index narrowing by whether
 * the customer carries an account reference. Options map submitted value =>
 * label; the panel renders the dropdown and applies query() server-side.
 */
class HasAccountRefFilter extends TableFilter
{
    public function key(): string
    {
        return 'has_account_ref';
    }

    public function label(): string
    {
        return 'Account ref (Example)';
    }

    /** @return array<string, string> */
    public function options(): array
    {
        return [
            'yes' => 'Has account ref',
            'no' => 'No account ref',
        ];
    }

    public function query(Builder $query, mixed $value): void
    {
        $value === 'yes'
            ? $query->whereNotNull('account_ref')->where('account_ref', '!=', '')
            : $query->where(fn (Builder $inner) => $inner->whereNull('account_ref')->orWhere('account_ref', ''));
    }
}
