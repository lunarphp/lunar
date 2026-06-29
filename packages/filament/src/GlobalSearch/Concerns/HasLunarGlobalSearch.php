<?php

namespace Lunar\Filament\GlobalSearch\Concerns;

use Filament\Resources\Resource;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Lunar\Filament\Forms\Components\Support\RecordSearch;

use function Filament\Support\generate_search_term_expression;

/**
 * Wire a Lunar `GlobalSearchDescriptor` into a Filament `Resource` in one
 * line. The consumer points `$globalSearch` at the descriptor and the trait
 * forwards every `getGlobalSearch*` method to it.
 *
 * @phpstan-require-extends Resource
 */
trait HasLunarGlobalSearch
{
    /**
     * Consumers MUST declare a `protected static string $globalSearch` on the
     * resource pointing at the matching `GlobalSearchDescriptor` subclass.
     *
     * The property is intentionally not declared in the trait — declaring it
     * here would clash with subclass redeclaration (PHP "incompatible
     * property" fatal). The trait reads `static::$globalSearch` at call time.
     */

    /**
     * @return array<int, string|array<int, string>>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return static::$globalSearch::getSearchableAttributes();
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return static::$globalSearch::getResultTitle($record);
    }

    /**
     * @return array<string, string|Htmlable|null>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return static::$globalSearch::getResultDetails($record);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return static::$globalSearch::getEloquentQuery();
    }

    protected static function applyGlobalSearchAttributeConstraints(Builder $query, string $search): void
    {
        if (RecordSearch::shouldUseScout(static::getModel())) {
            RecordSearch::applyScoutConstraint($query, $search, static::getModel());

            return;
        }

        /** @var Connection $connection */
        $connection = $query->getConnection();

        $search = generate_search_term_expression($search, static::isGlobalSearchForcedCaseInsensitive(), $connection);

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
