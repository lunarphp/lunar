<?php

namespace Lunar\Admin\Support\Resources;

use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Lunar\Admin\Support\Concerns;
use Lunar\Base\Traits\Searchable;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;

use function Filament\Support\generate_search_term_expression;

class BaseResource extends Resource
{
    use Concerns\CallsHooks;
    use Concerns\ExtendsForms;
    use Concerns\ExtendsPages;
    use Concerns\ExtendsRelationManagers;
    use Concerns\ExtendsSubnavigation;
    use Concerns\ExtendsTables;

    protected static ?string $permission = null;

    public static function registerNavigationItems(): void
    {
        if (! static::hasPermission()) {
            return;
        }

        parent::registerNavigationItems();
    }

    public static function can(string $action, ?Model $record = null): bool
    {
        return static::hasPermission();
    }

    protected static function hasPermission(): bool
    {
        if (! static::$permission) {
            return true;
        }

        $user = Filament::auth()->user();

        return $user->can(static::$permission);
    }

    /**
     * Override filament query builder
     */
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

            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $query->whereIn(
                'id',
                $ids
            );

            $query->when(
                ! $ids->isEmpty(),
                fn ($query) => $query->orderBySequence($ids->toArray())
            );

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

    /**
     * Return map hydrated with attributes
     *
     * @return array
     */
    protected static function mapSearchableAttributes(array &$map)
    {
        $attributes = Attribute::whereAttributeType(static::$model)
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
