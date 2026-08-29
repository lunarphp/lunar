<?php

namespace LunarPanelExample\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Customer;
use Lunar\Panel\Search\SearchSource;
use Lunar\Panel\Support\Position;

/**
 * A search source contributed by an add-on. Real add-ons index their own
 * entities (blog posts, tickets); this one searches customers by account
 * reference to show the seam without inventing a table.
 *
 * The resolver owns matching, so declaring the base query and the LIKE clauses
 * is enough: a store with the Scout path enabled gets it for free on any model
 * that is indexed.
 */
class CustomerEmailSearchSource extends SearchSource
{
    public function key(): string
    {
        return 'example-accounts';
    }

    public function label(): string
    {
        return __('example-addon::example.search_source');
    }

    public function icon(): string
    {
        return 'building';
    }

    /** The same handle gating this add-on's routes, so search cannot leak past it. */
    public function permission(): string
    {
        return 'sales:manage-customers';
    }

    public function position(): Position
    {
        return Position::last();
    }

    /** @return Builder<Customer> */
    public function query(): Builder
    {
        return Customer::query()->whereNotNull('account_ref');
    }

    public function applyTerm(Builder $query, string $token): void
    {
        $query->where('account_ref', 'like', "%{$token}%");
    }

    /** @param Customer $model */
    public function row(Model $model): array
    {
        return [
            'id' => $model->id,
            'label' => (string) $model->account_ref,
            'hint' => trim($model->first_name.' '.$model->last_name) ?: null,
            'url' => route('panel.customers.edit', $model),
        ];
    }
}
