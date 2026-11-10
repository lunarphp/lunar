<?php

namespace Lunar\Bundles\Panel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Bundles\Panel\Http\Requests\Concerns\RequiresBundle;
use Lunar\Core\Models\ProductVariant;

class SyncBundleComponentsRequest extends FormRequest
{
    use RequiresBundle;

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'components' => ['present', 'array'],
            'components.*.variant_id' => ['required', 'integer', 'exists:'.(new ProductVariant)->getTable().',id'],
            'components.*.quantity' => ['required', 'integer', 'min:1'],
            'components.*.group_id' => ['nullable', 'integer', 'exists:'.(new BundleGroup)->getTable().',id'],
            'components.*.default' => ['sometimes', 'boolean'],
            'components.*.position' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
