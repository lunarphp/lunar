<?php

namespace Lunar\Admin\Support\Forms\Components;

use Closure;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component as Livewire;
use Lunar\Admin\Support\Facades\AttributeData;
use Lunar\Base\FieldType;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;

class Attributes extends Group
{
    public ?string $modelClassOverride = null;

    protected string|Closure $attributeDataField = 'attribute_data';

    public function attributeDataField(string|Closure $attributeDataField): static
    {
        $this->attributeDataField = $attributeDataField;

        if (blank($this->relationship)) {
            $this->statePath($attributeDataField);
        }

        return $this;
    }

    public function getAttributeDataField(): string
    {
        return $this->evaluate($this->attributeDataField);
    }

    public function using(string $modelClass): self
    {
        $this->modelClassOverride = $modelClass;
        $this->key('attributeData'.class_basename($modelClass));

        return $this;
    }

    public function loadStateFromRelationships(bool $hydrateAll = false): void
    {
        parent::loadStateFromRelationships($hydrateAll);

        $rawState = $this->getRawState();

        if (! ($rawState instanceof Arrayable || is_array($rawState))) {
            return;
        }

        $data = $rawState instanceof Arrayable ? $rawState->toArray() : $rawState;

        foreach ($data as $key => $value) {
            if ($value instanceof FieldType) {
                $fieldValue = $value->getValue();
                if (is_scalar($fieldValue) || is_null($fieldValue)) {
                    $data[$key] = is_string($fieldValue) && blank($fieldValue) ? null : $fieldValue;
                }
            }
        }

        $this->rawState($data);
    }

    public function hydrateState(?array &$hydratedDefaultState, bool $shouldCallHydrationHooks = true, bool $shouldApplyStateCasts = true, array &$appliedStateCastPaths = []): void
    {
        if ($hydratedDefaultState === null) {
            $this->unwrapFieldTypeState();
        }

        parent::hydrateState($hydratedDefaultState, $shouldCallHydrationHooks, $shouldApplyStateCasts, $appliedStateCastPaths);
    }

    public function hydrateStatePartially(array $statePaths, bool $shouldCallHydrationHooks = true): void
    {
        $this->unwrapFieldTypeState();

        parent::hydrateStatePartially($statePaths, $shouldCallHydrationHooks);
    }

    protected function unwrapFieldTypeState(): void
    {
        $rawState = $this->getRawState();

        if (is_array($rawState) || $rawState instanceof Arrayable) {
            $rawState = collect($rawState)->map(
                fn ($value) => $value instanceof FieldType ? $value->getValue() : $value,
            )->toArray();

            $this->rawState($rawState);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->key('attributeData');
        $this->statePath('attribute_data');

        if (blank($this->childComponents['default'] ?? [])) {
            $this->schema(function (Get $get, Livewire $livewire, ?Model $record) {
                $modelClass = $this->modelClassOverride ?: $livewire::getResource()::getModel();

                $productTypeId = null;

                $morphMap = $modelClass::morphName();

                $attributeQuery = Attribute::where('attribute_type', $morphMap);

                // Products are unique in that they use product types to map attributes, so we need
                // to try and find the product type ID
                if ($morphMap == Product::morphName()) {
                    $productTypeId = $record?->product_type_id ?: ProductType::first()->id;

                    // If we have a product type, the attributes should be based off that.
                    if ($productTypeId) {
                        $attributeQuery = ProductType::find($productTypeId)->productAttributes();
                    }
                }

                if ($morphMap == ProductVariant::morphName()) {
                    if ($record::class === Product::modelClass()) {
                        $productTypeId = $record?->product_type_id ?: ProductType::first()->id;
                    } else {
                        $productTypeId = $record?->product?->product_type_id ?: ProductType::first()->id;
                    }

                    // If we have a product type, the attributes should be based off that.
                    if ($productTypeId) {
                        $attributeQuery = ProductType::find($productTypeId)->variantAttributes();
                    }
                }

                $attributes = $attributeQuery->orderBy('position')->get();

                $groups = AttributeGroup::where(
                    'attributable_type',
                    $morphMap
                )->orderBy('position', 'asc')
                    ->get()
                    ->map(function ($group) use ($attributes) {
                        return [
                            'model' => $group,
                            'fields' => $attributes->groupBy('attribute_group_id')->get($group->id, []),
                        ];
                    })
                    ->filter(fn ($group) => count($group['fields']));

                $groupComponents = [];

                foreach ($groups as $group) {
                    $sectionFields = [];

                    foreach ($group['fields'] as $field) {
                        $sectionFields[] = AttributeData::getFilamentComponent($field);
                    }
                    $groupComponents[] = Section::make($group['model']
                        ->translate('name'))
                        ->schema($sectionFields);
                }

                return $groupComponents;
            });
        }

        $this->mutateStateForValidationUsing(function ($state) {
            if (! is_array($state)) {
                return $state;
            }

            foreach ($state as $key => $value) {
                if (! $value instanceof FieldType) {
                    continue;
                }

                $state[$key] = $value->getValue();
            }

            return $state;
        });

        $this->mutateRelationshipDataBeforeSaveUsing(static function (Attributes $component, array $data): array {
            return [
                $component->getAttributeDataField() => $data,
            ];
        });

        $this->mutateRelationshipDataBeforeFillUsing(static function (Attributes $component, array $data): array {
            $attributeData = $data[$component->getAttributeDataField()] ?? [];

            foreach ($attributeData as $key => $value) {
                if ($value instanceof FieldType) {
                    $attributeData[$key] = $value->getValue();
                }
            }

            return $attributeData;
        });
    }
}
