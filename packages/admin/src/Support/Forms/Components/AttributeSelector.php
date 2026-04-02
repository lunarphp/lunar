<?php

namespace Lunar\Admin\Support\Forms\Components;

use Closure;
use Filament\Forms\Components\CheckboxList;
use Lunar\Facades\ModelManifest;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Product;
use Lunar\Models\ProductType;

class AttributeSelector extends CheckboxList
{
    protected string $view = 'lunarpanel::forms.components.attribute-selector';

    protected ?string $attributableType = null;

    public function withType($type)
    {
        $this->attributableType = $type;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadStateFromRelationships();
    }

    public function relationship(string|Closure|null $name = null, string|Closure|null $titleAttribute = null, ?Closure $modifyQueryUsing = null): static
    {
        $attributableType = $this->attributableType;

        parent::relationship($name, $titleAttribute ?? 'name', $modifyQueryUsing ?? static function ($query) use ($attributableType) {
            if ($attributableType) {
                return Attribute::query()->where('attribute_type', $attributableType);
            }

            return $query;
        });

        $type = $this->attributableType;

        $this->saveRelationshipsUsing(static function (CheckboxList $component, ?array $state) use ($type) {
            // Get all current mapped attributes
            $existing = $component->getRelationship()->get();

            $actualClass = ModelManifest::guessModelClass($type);
            // Filter out any that match this attribute type but are not in the saved state.
            $attributes = $existing->reject(
                fn ($attribute) => ! in_array($attribute->id, $state ?? []) && $attribute->attribute_type == $type
            )->pluck('id')->unique()->merge($state)->toArray();

            $component->getRelationship()->sync($attributes);
        });

        return $this;
    }

    public function getAttributeGroups()
    {
        $type = get_class(
            $this->getRelationship()->getParent()
        );

        if ($type === ProductType::morphName()) {
            $type = Product::morphName();
        }

        if ($this->attributableType) {
            $type = $this->attributableType;
        }

        return AttributeGroup::whereAttributableType($type)->orderBy('position')->get();
    }

    public function getSelectedAttributes($groupId)
    {
        return Attribute::where('attribute_group_id', $groupId)->whereIn('id', $this->getState())->get();
    }

    public function getAttributes($groupId)
    {
        return Attribute::where('attribute_group_id', $groupId)->orderBy('position')->get();
    }

    /**
     * Disable the "in" validation since multiple AttributeSelector fields
     * share the same state path and each validates against a different
     * attribute type. The custom saveRelationshipsUsing callback handles
     * filtering by type.
     *
     * @return ?array<string>
     */
    public function getInValidationRuleValues(): ?array
    {
        return null;
    }
}
