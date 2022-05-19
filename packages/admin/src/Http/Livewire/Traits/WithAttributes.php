<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use GetCandy\FieldTypes\Number;
use GetCandy\FieldTypes\Text;
use GetCandy\FieldTypes\TranslatedText;
use GetCandy\Models\AttributeGroup;
use GetCandy\Models\Language;
use Illuminate\Support\Collection;

trait WithAttributes
{
    /**
     * The attribute mapping for editing.
     *
     * @var array
     */
    public $attributeMapping = [];

    /**
     * Mount the WithAttributes trait.
     *
     * @return void
     */
    public function mountWithAttributes()
    {
        $this->mapAttributes();
    }

    /**
     * Listen for when product type id is updated.
     *
     * @return void
     */
    public function updatedProductProductTypeId()
    {
        $this->mapAttributes();
    }

    protected function mapAttributes()
    {
        $this->attributeMapping = $this->parseAttributes(
            $this->availableAttributes,
            $this->attributeData
        );
    }

    /**
     * Parse the attributes into the correct collection format.
     *
     * @param  \Illuminate\Support\Collection  $attributes
     * @return \Illuminate\Support\Collection
     */
    protected function parseAttributes(Collection $attributes, $existingData, $key = 'attributeMapping')
    {
        return $attributes->reject(function ($attribute) {
            return ! class_exists($attribute->type);
        })->mapWithKeys(function ($attribute) use ($key, $existingData) {
            $data = $existingData ?
                $existingData->first(fn ($value, $handle) => $handle == $attribute->handle)
                : null;

            $value = $data ? $data->getValue() : null;
            // We need to make sure we give livewire all the languages if we're trying to translate.
            if ($attribute->type == TranslatedText::class) {
                $value = $this->prepareTranslatedText($value);
            }

            $reference = 'a_'.$attribute->id;

            return [$reference => [
                'name'           => $attribute->translate('name'),
                'group'          => $attribute->attributeGroup->translate('name'),
                'group_id'       => $attribute->attributeGroup->id,
                'group_handle'   => $attribute->attributeGroup->handle,
                'group_position' => $attribute->attributeGroup->position,
                'id'             => $attribute->handle,
                'signature'      => "{$key}.{$reference}.data",
                'type'           => $attribute->type,
                'handle'         => $attribute->handle,
                'configuration'  => $attribute->configuration,
                'required'       => $attribute->required,
                'view'           => app()->make($attribute->type)->getView(),
                'validation'     => $attribute->validation_rules,
                'data'           => $value,
            ]];
        });
    }

    public function getAttributeGroupsProperty()
    {
        $groupIds = $this->attributeMapping->pluck('group_id')->unique();

        return AttributeGroup::whereIn('id', $groupIds)
            ->orderBy('position')
            ->get()->map(function ($group) {
                return [
                    'model'  => $group,
                    'fields' => $this->attributeMapping->filter(fn ($att) => $att['group_id'] == $group->id),
                ];
            });
    }

    /**
     * Prepares attribute data to be ready for saving.
     *
     * @return \Illuminate\Support\Collection
     */
    public function prepareAttributeData($attributes = null)
    {
        return collect(($attributes ?? $this->attributeMapping))->mapWithKeys(function ($attribute) {
            $value = null;
            switch ($attribute['type']) {
                case TranslatedText::class:
                    $value = $this->mapTranslatedText($attribute['data']);
                    break;

                default:
                    $value = new $attribute['type']($attribute['data']);
                    break;
            }

            return [
                $attribute['handle'] => $value,
            ];
        });
    }

    /**
     * Map translated values into field types.
     *
     * @param  array  $data
     * @return \GetCandy\FieldTypes\TranslatedText
     */
    protected function mapTranslatedText($data)
    {
        $values = [];
        foreach ($data as $code => $value) {
            $values[$code] = new Text($value);
        }

        return new TranslatedText(collect($values));
    }

    /**
     * Prepare translated text field for Livewire modeling.
     *
     * @param  string|array  $value
     * @return array
     */
    protected function prepareTranslatedText($value)
    {
        foreach ($this->languages as $language) {
            // If we've changed from Text to TranslatedText we might
            // have a string value. In this case we want to convert it to translated text.
            if (is_string($value)) {
                $newValue = collect();
                if ($language->default) {
                    $newValue[$language->code] = $value;
                }
                $value = $newValue;
                continue;
            }

            if (empty($value[$language->code])) {
                $value[$language->code] = null;
            }
        }

        return $value;
    }

    public function withAttributesValidationRules()
    {
        $rules = [];
        foreach ($this->attributeMapping as $index => $attribute) {
            if (! class_exists($attribute['type'])) {
                continue;
            }

            $validation = $attribute['validation'] ? explode(',', $attribute['validation']) : [];

            $field = $attribute['signature'];

            if (($attribute['required'] ?? false) || ($attribute['system'] ?? false)) {
                if ($attribute['type'] == TranslatedText::class) {
                    // Get the default language and make that the only one required.
                    $field = "{$attribute['signature']}.{$this->defaultLanguage->code}";
                }

                $validation = array_merge($validation, ['required']);
            }

            if ($attribute['type'] == Number::class) {
                $validation = array_merge($validation, [
                    'numeric'.($attribute['configuration']['min'] ? '|min:'.$attribute['configuration']['min'] : ''),
                    'numeric'.($attribute['configuration']['max'] ? '|max:'.$attribute['configuration']['max'] : ''),
                ]);
            }

            $rules[$field] = implode('|', $validation);
        }

        return $rules;
    }

    /**
     * Return extra validation messages.
     *
     * @return array
     */
    protected function withAttributesValidationMessages()
    {
        $messages = [];
        foreach ($this->attributeMapping as $index => $attribute) {
            if (($attribute['required'] ?? false) || ($attribute['system'] ?? false)) {
                if ($attribute['type'] == TranslatedText::class) {
                    $messages["attributeMapping.{$index}.data.{$this->defaultLanguage->code}.required"] =
                        __('adminhub::validation.generic_required');
                    continue;
                }
                $messages["attributeMapping.{$index}.data.required"] = __('adminhub::validation.generic_required');
            }
        }

        return $messages;
    }

    /**
     * Computed property to get attribute data.
     *
     * @return array
     */
    abstract public function getAttributeDataProperty();

    /**
     * Computed property to get available attributes.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract public function getAvailableAttributesProperty();

    abstract public function getLanguagesProperty();
}
