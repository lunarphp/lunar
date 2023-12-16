<?php

namespace Lunar\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Facades\AttributeManifest;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Search\Interfaces\ScoutIndexerInterface;

class ScoutIndexer implements ScoutIndexerInterface
{
    public function searchableAs(Model $model): string
    {
        $name = str_replace('lunar_', '', $model->getTable());

        return config('scout.prefix').$name;
    }

    public function shouldBeSearchable(Model $model): bool
    {
        return true;
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query;
    }

    public function getScoutKey(Model $model): mixed
    {
        return $model->getKey();
    }

    public function getScoutKeyName(Model $model): mixed
    {
        return $model->getKeyName();
    }

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

    public function toSearchableArray(Model $model): array
    {
        if (! $model->attribute_data) {
            $data = $model->toArray();
        } else {
            $data = $this->mapSearchableAttributes($model);
        }

        return array_merge([
            'id' => $model->id,
        ], $data);
    }

    protected function mapSearchableAttributes(Model $model): array
    {
        $attributes = AttributeManifest::getSearchableAttributes(
            $model->getMorphClass()
        );

        $attributeData = $model->attribute_data;

        $data = [];

        foreach ($attributes as $attribute) {
            $attributeValue = $attributeData->get($attribute->handle);

            if ($attributeValue instanceof TranslatedText) {
                foreach ($attributeValue->getValue() as $locale => $text) {
                    $data[$attribute->handle.'_'.$locale] = $text?->getValue();
                }

                continue;
            }

            $data[$attribute->handle] = $model->attr($attribute->handle);
        }

        return $data;
    }
}
