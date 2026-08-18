<?php

namespace Lunar\Panel\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Lunar\Core\Models\ProductVariant;
use Lunar\Panel\Support\VariantFields;

/**
 * Rules for the variant update endpoint and the drafts layer — the field
 * surface is defined once in VariantFields. Unknown keys are dropped by
 * validation, so a stray field never reaches the update action.
 */
class ProductVariantRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var ProductVariant $variant */
        $variant = $this->route('productVariant');

        return app(VariantFields::class)->rules($variant);
    }

    /**
     * The validated payload, normalised the same way the drafts layer
     * normalises values, so the update endpoint and a draft commit write
     * identical shapes.
     *
     * @return array<string, mixed>
     */
    public function variantAttributes(): array
    {
        return app(VariantFields::class)->normalizeAll($this->validated());
    }
}
