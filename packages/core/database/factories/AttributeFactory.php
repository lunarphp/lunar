<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;

class AttributeFactory extends BaseFactory
{
    private static $position = 1;

    protected $model = Attribute::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'attribute_group_id' => AttributeGroup::factory(),
            'position' => self::$position++,
            'name' => $this->faker->words(2, true),
            'handle' => $this->faker->unique()->slug(),
            'type' => FieldTypeEnum::Text->value,
            'configuration' => [],
            'required' => false,
            'validation_rules' => null,
            'searchable' => false,
            'filterable' => false,
            'system' => false,
        ];
    }

    /**
     * @param  array<int, string>  $rules
     */
    public function validationRules(array $rules): static
    {
        return $this->state(['validation_rules' => $rules]);
    }

    /**
     * Attach the attribute to a model type via the attribute_models table.
     */
    public function modelType(string $morph): static
    {
        return $this->afterCreating(function (Attribute $attribute) use ($morph) {
            $attribute->models()->create(['model_type' => $morph]);
        });
    }
}
