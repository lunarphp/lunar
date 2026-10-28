<?php

namespace Lunar\Bundles\Panel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Bundles\Enums\BundlePricing;

class DefineBundleRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'pricing' => ['required', Rule::enum(BundlePricing::class)],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
