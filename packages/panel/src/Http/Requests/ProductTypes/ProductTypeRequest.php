<?php

namespace Lunar\Panel\Http\Requests\ProductTypes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeModel;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\States\ProductType\Active;
use Lunar\Core\States\ProductType\Draft;

/** Shared by the product type store and update endpoints, whose rules are identical. */
class ProductTypeRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return static::rulesFor($this->route('productType'));
    }

    /**
     * The rule set, parameterised on the type being edited so the drafts
     * layer can validate a commit payload with the same rules the update
     * endpoint applies. On create (null) the handle may be omitted — the
     * model generates one from the name. The two attribute id sets validate
     * against the matching morph's attributes, so a product attribute id
     * posted in the variant list is rejected rather than silently re-homed.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rulesFor(?ProductType $productType): array
    {
        $unique = Rule::unique((new ProductType)->getTable(), 'handle');

        if ($productType) {
            $unique->ignore($productType->getKey());
        }

        $attributeModels = (new AttributeModel)->getTable();

        return [
            'name' => ['required', 'string', 'max:255'],
            'handle' => [
                $productType ? 'required' : 'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $unique,
            ],
            'status' => ['nullable', Rule::in([Active::$name, Draft::$name])],
            'description' => ['nullable', 'string', 'max:65535'],
            'default_tax_class_id' => ['nullable', Rule::exists((new TaxClass)->getTable(), 'id')],
            'product_attribute_ids' => ['nullable', 'array'],
            'product_attribute_ids.*' => [
                'integer',
                Rule::exists($attributeModels, 'attribute_id')->where('model_type', Product::morphName()),
            ],
            'variant_attribute_ids' => ['nullable', 'array'],
            'variant_attribute_ids.*' => [
                'integer',
                Rule::exists($attributeModels, 'attribute_id')->where('model_type', ProductVariant::morphName()),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function productTypeAttributes(): array
    {
        return collect($this->validated())
            ->except(['product_attribute_ids', 'variant_attribute_ids'])
            ->reject(fn (mixed $value, string $key) => $key === 'handle' && blank($value))
            ->reject(fn (mixed $value, string $key) => $key === 'status' && blank($value))
            ->all();
    }

    /**
     * The full attribute mapping to sync, or null when the request touched
     * neither id set. A set the request omitted keeps the type's current
     * mapping for that surface, so posting only product attributes never
     * clears the variant mapping.
     *
     * @return ?array<int, int>
     */
    public function attributeMappingIds(?ProductType $productType): ?array
    {
        $validated = $this->validated();

        if (! array_key_exists('product_attribute_ids', $validated)
            && ! array_key_exists('variant_attribute_ids', $validated)) {
            return null;
        }

        // Plucked through the filtered relations (allRelatedIds() queries the
        // pivot directly and would skip the morph filter); the key is
        // qualified because the pivot join makes a bare `id` ambiguous.
        $attributeKey = (new Attribute)->getQualifiedKeyName();

        $productIds = array_key_exists('product_attribute_ids', $validated)
            ? array_map('intval', $validated['product_attribute_ids'] ?? [])
            : ($productType?->productAttributes()->pluck($attributeKey)->all() ?? []);

        $variantIds = array_key_exists('variant_attribute_ids', $validated)
            ? array_map('intval', $validated['variant_attribute_ids'] ?? [])
            : ($productType?->variantAttributes()->pluck($attributeKey)->all() ?? []);

        return array_values(array_unique([...$productIds, ...$variantIds]));
    }
}
