<?php

namespace Lunar\Panel\Search\Sources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Customer;
use Lunar\Panel\Search\SearchSource;
use Lunar\Panel\Sections\Sales\SalesSection;
use Lunar\Panel\Support\Position;

class CustomerSearchSource extends SearchSource
{
    public function key(): string
    {
        return 'customers';
    }

    public function label(): string
    {
        return __('panel::search.source_customers');
    }

    public function icon(): string
    {
        return 'users';
    }

    public function permission(): string
    {
        return SalesSection::CUSTOMERS_PERMISSION;
    }

    public function position(): Position
    {
        return Position::priority(50);
    }

    /** @return Builder<Customer> */
    public function query(): Builder
    {
        return Customer::query()->with(['users:id,email']);
    }

    public function applyTerm(Builder $query, string $token): void
    {
        $like = "%{$token}%";

        $query->where('first_name', 'like', $like)
            ->orWhere('last_name', 'like', $like)
            ->orWhere('company_name', 'like', $like)
            ->orWhere('tax_identifier', 'like', $like)
            ->orWhere('account_ref', 'like', $like)
            ->orWhereHas('users', fn (Builder $query) => $query->where('email', 'like', $like));
    }

    /** @param Customer $model */
    public function row(Model $model): array
    {
        $name = trim($model->first_name.' '.$model->last_name);

        return [
            'id' => $model->id,
            'label' => $name ?: (string) $model->company_name,
            'hint' => implode(' · ', array_filter([
                $name !== '' ? $model->company_name : null,
                $model->users->first()?->email,
            ])) ?: null,
            'url' => route('panel.customers.edit', $model),
        ];
    }
}
