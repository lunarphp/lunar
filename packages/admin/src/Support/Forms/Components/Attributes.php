<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component as Livewire;
use Lunar\Admin\Support\Facades\AttributeData;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;

class Attributes extends Forms\Components\Group
{
    public ?string $modelClassOverride = null;

    public function using(string $modelClass): self
    {
        $this->modelClassOverride = $modelClass;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->key('attributeData'.$this->modelClassOverride);

        if (blank($this->childComponents)) {
            $this->schema(function (\Filament\Forms\Get $get, Livewire $livewire, ?Model $record) {
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
                    $groupComponents[] = Forms\Components\Section::make($group['model']->translate('name'))
                        ->schema($sectionFields)
                        ->statePath('attribute_data');
                }

                return $groupComponents;
            });
        }

        $this->mutateStateForValidationUsing(function ($state) {
            if (! is_array($state)) {
                return $state;
            }

            foreach ($state as $key => $value) {
                if (! $value instanceof \Lunar\Base\FieldType) {
                    continue;
                }

                $state[$key] = $value->getValue();
            }

            return $state;
        });
    }
}
