<?php

namespace Lunar\Panel\Search;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Panel\Support\Position;

/**
 * One entity the global search can find records in. A source declares the base
 * query, how a term narrows it, and how a matched record reads as a result row;
 * the resolver owns matching strategy (tokenised LIKE, or Scout keys when the
 * model is indexed and the config flag is on) and the per-source limit.
 */
abstract class SearchSource
{
    /** Stable identifier, also the `kinds[]` filter value and the group key in the UI. */
    abstract public function key(): string;

    /** Group heading in the palette, e.g. 'Products'. */
    abstract public function label(): string;

    /**
     * Base query — model, eager loads, scopes. Both matching paths start here,
     * so a constraint added here applies however results are matched.
     *
     * @return Builder<covariant Model>
     */
    abstract public function query(): Builder;

    /**
     * Narrow the query by one search token. The resolver splits the term and
     * calls this once per token, ANDing them, so word order does not matter.
     *
     * @param  Builder<covariant Model>  $query
     */
    abstract public function applyTerm(Builder $query, string $token): void;

    /**
     * The result row for a matched record. `hint` is the disambiguating detail
     * (an order's customer, a product's SKU) shown under the label.
     *
     * @return array{id: int|string, label: string, hint: ?string, url: string}
     */
    abstract public function row(Model $model): array;

    /**
     * Manifest permission handle gating this source, matching the handle on the
     * section's routes so search can never surface a record the user could not
     * open. Null makes the source visible to every panel user.
     */
    public function permission(): ?string
    {
        return null;
    }

    public function icon(): string
    {
        return 'search';
    }

    public function position(): Position
    {
        return Position::last();
    }

    public function visible(?Authenticatable $user = null): bool
    {
        if ($permission = $this->permission()) {
            return $user !== null && $user->can($permission);
        }

        return true;
    }
}
