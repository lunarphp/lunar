<?php

namespace Lunar\Filament\Forms\Components\Support;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder as ScoutBuilder;
use Laravel\Scout\Searchable;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\Models\Attribute;

use function Filament\Support\generate_search_column_expression;
use function Filament\Support\generate_search_term_expression;

/**
 * Single search backend shared by every Lunar selector component.
 *
 * Prefers Scout when both `lunar.admin.scout_enabled` is true and the
 * model uses Laravel Scout's `Searchable` trait. Falls back to a
 * translated-attribute DB query across the model's searchable attributes.
 */
class RecordSearch
{
    /**
     * Build a search query for the given model and term.
     *
     * @param  class-string<Model>  $model
     */
    public static function for(string $model, string $search): ScoutBuilder|Builder
    {
        if (static::shouldUseScout($model)) {
            return $model::search($search);
        }

        return static::translatedAttributeQuery($model, $search);
    }

    /**
     * Whether Scout should be used for the given model.
     *
     * @param  class-string<Model>  $model
     */
    public static function shouldUseScout(string $model): bool
    {
        if (! config('lunar.admin.scout_enabled', false)) {
            return false;
        }

        return in_array(Searchable::class, class_uses_recursive($model), true);
    }

    /**
     * Apply a Scout global-search constraint to an Eloquent query, narrowing
     * it to the Scout-returned ids in score order.
     *
     * @param  class-string<Model>  $model
     */
    public static function applyScoutConstraint(Builder $query, string $search, string $model): void
    {
        $ids = collect($model::search($search)->keys())->map(
            fn ($result) => str_replace($model.'::', '', $result)
        );

        $query
            ->whereIn('id', $ids)
            ->orderBySequence($ids);
    }

    /**
     * Merge translated searchable attribute paths (each pointing at a
     * searchable `TranslatedText` attribute via `attribute_data->{handle}->value`)
     * into the resource-supplied list.
     *
     * @param  class-string<Model>  $model
     * @param  array<int, string|array<int, string>>  $resourceAttributes
     * @return array<int, string|array<int, string>>
     */
    public static function globallySearchableAttributes(string $model, array $resourceAttributes): array
    {
        $translated = Attribute::query()
            ->whereHas('models', fn ($query) => $query->where('model_type', $model::morphName()))
            ->where('searchable', true)
            ->get()
            ->filter(fn (Attribute $attribute): bool => $attribute->type === FieldTypeEnum::TranslatedText->value)
            ->map(fn (Attribute $attribute): string => 'attribute_data->'.$attribute->id)
            ->values()
            ->all();

        return array_values(array_merge($resourceAttributes, $translated));
    }

    /**
     * Build a translated-attribute DB query mirroring the legacy
     * `get_search_builder()` helper.
     *
     * @param  class-string<Model>  $model
     */
    protected static function translatedAttributeQuery(string $model, string $search): Builder
    {
        $query = $model::query();

        /** @var Connection $connection */
        $connection = $query->getConnection();

        $search = generate_search_term_expression($search, true, $connection);

        foreach (explode(' ', $search) as $word) {
            $query->where(function (Builder $query) use ($model, $word, $connection): void {
                $columns = static::searchableTranslatedColumns($model);

                $first = true;

                foreach ($columns as $column) {
                    $method = $first ? 'where' : 'orWhere';

                    $query->{$method}(
                        generate_search_column_expression($query->qualifyColumn($column), true, $connection),
                        'like',
                        "%{$word}%",
                    );

                    $first = false;
                }
            });
        }

        return $query;
    }

    /**
     * Resolve the columns to search across. Includes every searchable
     * TranslatedText attribute (via `attribute_data->{handle}->value`),
     * and falls back to the plain `name` column when no translated
     * attributes are configured for the model.
     *
     * @param  class-string<Model>  $model
     * @return array<int, string>
     */
    protected static function searchableTranslatedColumns(string $model): array
    {
        $columns = Attribute::query()
            ->whereHas('models', fn ($query) => $query->where('model_type', $model::morphName()))
            ->where('searchable', true)
            ->get()
            ->filter(fn (Attribute $attribute): bool => $attribute->type === FieldTypeEnum::TranslatedText->value)
            ->map(fn (Attribute $attribute): string => 'attribute_data->'.$attribute->id)
            ->values()
            ->all();

        if ($columns === [] && static::modelHasColumn($model, 'name')) {
            return ['name'];
        }

        return $columns;
    }

    /**
     * @param  class-string<Model>  $model
     */
    protected static function modelHasColumn(string $model, string $column): bool
    {
        $instance = new $model;

        return $instance->getConnection()
            ->getSchemaBuilder()
            ->hasColumn($instance->getTable(), $column);
    }
}
