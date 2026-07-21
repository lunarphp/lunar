<?php

namespace Lunar\Panel\Http\Requests\ProductTypes;

use Illuminate\Foundation\Http\FormRequest;

class ProductTypeMediaUpdateRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'alt' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'focal' => ['nullable', 'array:x,y'],
            'focal.x' => ['required_with:focal', 'integer', 'between:0,100'],
            'focal.y' => ['required_with:focal', 'integer', 'between:0,100'],
            'primary' => ['nullable', 'boolean'],
        ];
    }
}
