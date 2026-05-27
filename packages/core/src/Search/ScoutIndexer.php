<?php

namespace Lunar\Core\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lunar\Core\Facades\AttributeManifest;
use Lunar\Core\FieldTypes\TranslatedText;
use Lunar\Core\Search\Interfaces\ScoutIndexerInterface;

class ScoutIndexer implements ScoutIndexerInterface
{
    public function searchableAs(Model $model): string
    {
        $tablePrefix = config('lunar.database.table_prefix');
        $name = str_replace($tablePrefix, '', $model->getTable());

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
            'id' => (string) $model->id,
        ], $data);
    }

    /**
     * Explode dedicated translatable columns (`name`, `description`, …) into
     * per-locale index keys (`name_en`, `name_fr`, …), mirroring how
     * translatable custom attributes are indexed. A plain string column (e.g.
     * Brand's non-translatable `name`) is indexed under its bare handle.
     *
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    protected function mapTranslatableFields(Model $model, array $fields): array
    {
        $data = [];

        foreach ($fields as $field) {
            $value = $model->getAttribute($field);

            if ($value instanceof Collection || is_array($value)) {
                foreach ($value as $locale => $text) {
                    $data[$field.'_'.$locale] = $text;
                }

                continue;
            }

            if (filled($value)) {
                $data[$field] = $value;
            }
        }

        return $data;
    }

    protected function mapSearchableAttributes(Model $model): array
    {
        $attributes = AttributeManifest::getSearchableAttributes(
            $model->getMorphClass()
        );

        $attributeData = $model->attribute_data;

        if (! $attributeData) {
            return [];
        }

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
