<?php

namespace Lunar\Bundles\Panel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Bundles\Panel\Http\Requests\Concerns\RequiresBundle;

class SyncBundleGroupsRequest extends FormRequest
{
    use RequiresBundle;

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'groups' => ['present', 'array'],
            'groups.*.id' => ['nullable', 'integer', 'exists:'.(new BundleGroup)->getTable().',id'],
            'groups.*.name' => ['required', 'array'],
            'groups.*.name.*' => ['nullable', 'string', 'max:255'],
            'groups.*.min_selections' => ['required', 'integer', 'min:0'],
            'groups.*.max_selections' => ['required', 'integer', 'min:1'],
            'groups.*.position' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
