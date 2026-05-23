<?php

namespace Lunar\Admin\Support\Resources\Concerns;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Lunar\Filament\Forms\Components\Support\RecordSearch;

use function Filament\Support\generate_search_term_expression;

/**
 * @deprecated since v2 — replaced by `Lunar\Filament\GlobalSearch\Concerns\HasLunarGlobalSearch`
 * paired with a per-model `GlobalSearchDescriptor`. This trait now delegates
 * to `RecordSearch`; the trait will be removed in v3.
 */
trait HasScoutGlobalSearch
{
    protected static function applyGlobalSearchAttributeConstraints(Builder $query, string $search): void
    {
        if (RecordSearch::shouldUseScout(static::getModel())) {
            RecordSearch::applyScoutConstraint($query, $search, static::getModel());

            return;
        }

        /** @var Connection $databaseConnection */
        $databaseConnection = $query->getConnection();

        $search = generate_search_term_expression($search, static::isGlobalSearchForcedCaseInsensitive(), $databaseConnection);

        $attributes = RecordSearch::globallySearchableAttributes(
            static::getModel(),
            static::getGloballySearchableAttributes(),
        );

        foreach (explode(' ', $search) as $searchWord) {
            $query->where(function (Builder $query) use ($searchWord, $attributes) {
                $isFirst = true;

                foreach ($attributes as $attribute) {
                    static::applyGlobalSearchAttributeConstraint(
                        query: $query,
                        search: $searchWord,
                        searchAttributes: Arr::wrap($attribute),
                        isFirst: $isFirst,
                    );

                    $isFirst = false;
                }
            });
        }
    }
}
