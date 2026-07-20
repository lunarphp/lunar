<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

/** Shared by the tax class store and update endpoints, whose rules are identical. */
class TaxClassRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'default' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * The validated input shaped for the tax class actions: the default flag
     * cast, and omitted entirely when not supplied so an update leaves it
     * untouched.
     *
     * @return array<string, mixed>
     */
    public function taxClassAttributes(): array
    {
        $validated = $this->validated();

        $attributes = ['name' => $validated['name']];

        if (array_key_exists('default', $validated)) {
            $attributes['default'] = (bool) $validated['default'];
        }

        return $attributes;
    }
}
