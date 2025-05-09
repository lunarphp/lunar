<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Lunar\Base\Traits\Searchable;
use Lunar\DataTypes\Price;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Spatie\LaravelBlink\BlinkFacade;

if (! function_exists('price')) {
    function price($value, $currency, $unitQty = 1)
    {
        return new Price($value, $currency, $unitQty);
    }
}

if (! function_exists('sync_with_search')) {
    function sync_with_search(?Illuminate\Database\Eloquent\Model $model = null): void
    {
        if (! $model) {
            return;
        }

        $isSearchable = in_array(Searchable::class, class_uses_recursive($model));

        if ($isSearchable) {
            $model->searchable();

            return;
        }

        if ($model instanceof \Lunar\Models\ProductVariant) {
            $model->product()->first()->searchable();
        }

        if ($model instanceof \Lunar\Models\Address) {
            $model->customer()->first()->searchable();
        }

        if (is_lunar_user($model)) {
            foreach ($model->customers()->get() as $customer) {
                $customer->searchable();
            }
        }
    }
}

if (! function_exists('db_date')) {
    function db_date($column, $format, $alias = null)
    {
        $connection = config('database.default');

        $driver = config("database.connections.{$connection}.driver");

        $select = "DATE_FORMAT({$column}, '{$format}')";

        if ($driver == 'pgsql') {
            $format = str_replace('%', '', $format);
            $select = "TO_CHAR({$column} :: DATE, '{$format}')";
        }

        if ($driver == 'sqlite') {
            $select = "strftime('{$format}', {$column})";
        }

        if ($alias) {
            $select .= " as {$alias}";
        }

        return $select;
    }
}

if (! function_exists('get_search_builder')) {
    function get_search_builder($model, $search): Laravel\Scout\Builder|Builder
    {
        $scoutEnabled = config('lunar.panel.scout_enabled', false);
        $isScoutSearchable = in_array(Searchable::class, class_uses_recursive($model));

        if (
            $scoutEnabled &&
            $isScoutSearchable
        ) {
            return $model::search($search);
        } else {
            $query = $model::query();

            $search = Str::lower($search);

            foreach (explode(' ', $search) as $searchWord) {
                $query->where(function (Builder $query) use ($model, $searchWord) {
                    $attributes = Attribute::whereAttributeType($model::morphName())
                        ->whereSearchable(true)
                        ->get();

                    $searchableAttributes = [];

                    foreach ($attributes as $attribute) {
                        if ($attribute->type == TranslatedText::class || $attribute->type == Text::class) {
                            array_push($searchableAttributes, $attribute->handle);
                        }
                    }

                    $isFirst = true;

                    foreach ($searchableAttributes as $searchAttribute) {
                        $whereClause = $isFirst ? 'whereJsonContainsInsensitive' : 'orWhereJsonContainsInsensitive';

                        $query->{$whereClause}('attribute_data', [$searchAttribute, 'value'], $searchWord);

                        $isFirst = false;
                    }
                });
            }

            return $query;
        }
    }
}

if (! function_exists('apply_attribute_search')) {
    function apply_attribute_search(Builder $query, string $search, string $modelMorphName): void
    {
        $attributes = fn ($modelMorphName) => BlinkFacade::once('lunar_searchable_attributes_'.$modelMorphName,
            fn () => Attribute::whereAttributeType($modelMorphName)->whereSearchable(true)->get());

        $searchableAttributes = [];

        foreach ($attributes as $attribute) {
            if ($attribute->type == TranslatedText::class || $attribute->type == Text::class) {
                array_push($searchableAttributes, $attribute->handle);
            }
        }

        foreach (explode(' ', $search) as $searchWord) {
            $isFirst = true;

            foreach ($searchableAttributes as $searchAttribute) {
                $whereClause = $isFirst ? 'whereJsonContainsInsensitive' : 'orWhereJsonContainsInsensitive';

                $query->{$whereClause}('attribute_data', [$searchAttribute, 'value'], $searchWord);

                $isFirst = false;
            }
        }
    }
}
