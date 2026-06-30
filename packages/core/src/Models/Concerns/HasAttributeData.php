<?php

namespace Lunar\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Lunar\Core\Casts\AsAttributeData;
use Lunar\Core\Contracts\FieldType;
use Lunar\Core\Models\Attribute;

trait HasAttributeData
{
    public function initializeHasAttributeData(): void
    {
        $this->mergeCasts([
            'attribute_data' => AsAttributeData::class,
        ]);
    }

    /**
     * The attributes applicable to this model's morph type.
     *
     * @return EloquentCollection<int, Attribute>
     */
    public function mappedAttributes(): EloquentCollection
    {
        return Attribute::query()
            ->whereHas('models', fn ($query) => $query->where('model_type', static::morphName()))
            ->orderBy('position')
            ->get();
    }

    /**
     * @return EloquentCollection<int, Attribute>
     */
    public function getMappedAttributesAttribute(): EloquentCollection
    {
        return $this->mappedAttributes();
    }

    /**
     * Translate a value from the attribute data.
     */
    public function translateAttribute(string $attribute, ?string $locale = null): mixed
    {
        $field = Arr::get($this->getAttribute('attribute_data'), $attribute);

        if (! $field) {
            return null;
        }

        $translations = $field->getValue();

        if (! is_iterable($translations) || ! $translations) {
            return $translations;
        }

        $value = Arr::get($translations, $locale ?: app()->getLocale(), Arr::first($translations));

        if (! $value) {
            return null;
        }

        if (! $value instanceof FieldType) {
            return $field->getValue();
        }

        if (! $value->getValue()) {
            return $translations->first(
                fn ($value) => $value->getValue()
            )?->getValue();
        }

        return $value->getValue();
    }

    /**
     * Shorthand to translate an attribute.
     */
    public function attr(string $attribute, ?string $locale = null): mixed
    {
        return $this->translateAttribute($attribute, $locale);
    }
}
