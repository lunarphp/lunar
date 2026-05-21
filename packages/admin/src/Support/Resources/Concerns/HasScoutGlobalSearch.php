<?php

namespace Lunar\Admin\Support\Resources\Concerns;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Lunar\Core\Base\Traits\Searchable;
use Lunar\Core\FieldTypes\TranslatedText;
use Lunar\Core\Models\Attribute;

use function Filament\Support\generate_search_term_expression;

trait HasScoutGlobalSearch
{
    protected static function applyGlobalSearchAttributeConstraints(Builder $query, string $search): void
    {
        $scoutEnabled = config('lunar.panel.scout_enabled', false);
        $isScoutSearchable = in_array(Searchable::class, class_uses_recursive(static::getModel()));

        if (
            $scoutEnabled &&
            $isScoutSearchable
        ) {
            $ids = collect(static::getModel()::search($search)->keys())->map(
                fn ($result) => str_replace(static::getModel().'::', '', $result)
            );

            $query
                ->whereIn('id', $ids)
                ->orderBySequence($ids);
        } else {
            /** @var Connection $databaseConnection */
            $databaseConnection = $query->getConnection();

            $search = generate_search_term_expression($search, static::isGlobalSearchForcedCaseInsensitive(), $databaseConnection);

            foreach (explode(' ', $search) as $searchWord) {
                $query->where(function (Builder $query) use ($searchWord) {
                    $isFirst = true;

                    $searchableAttributes = static::getGloballySearchableAttributes();

                    static::mapSearchableAttributes($searchableAttributes);

                    foreach ($searchableAttributes as $attributes) {
                        static::applyGlobalSearchAttributeConstraint(
                            query: $query,
                            search: $searchWord,
                            searchAttributes: Arr::wrap($attributes),
                            isFirst: $isFirst,
                        );
                    }
                });
            }
        }
    }

    protected static function mapSearchableAttributes(array &$map): array
    {
        $attributes = Attribute::whereAttributeType(
            static::getModel()::morphName()
        )
            ->whereSearchable(true)
            ->get();

        foreach ($attributes as $attribute) {
            if ($attribute->type == TranslatedText::class) {
                array_push($map, 'attribute_data->'.$attribute->handle.'->value');
            }
        }

        return $map;
    }
}
