<?php

namespace Lunar\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductOptionIndexer extends ScoutIndexer
{
    public function getSortableFields(): array
    {
        return [
            'created_at',
            'updated_at',
        ];
    }

    public function getFilterableFields(): array
    {
        return [
            '__soft_deleted',
        ];
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query;
    }

    public function toSearchableArray(Model $model): array
    {
        $data['id'] = (string) $model->id;

        // Loop for add option name
        foreach ($model->name as $locale => $name) {
            $data['name_'.$locale] = $name;
        }

        // Loop for add option label
        foreach ($model->name as $locale => $name) {
            $data['label_'.$locale] = $name;
        }

        // Loop for add options
        foreach ($model->values as $option) {
            foreach ($option->name as $locale => $name) {
                $key = 'option_'.$option->id.'_'.$locale;
                $data[$key] = $name;
            }
        }

        return $data;
    }
}
