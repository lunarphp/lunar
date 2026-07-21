<?php

namespace Lunar\Panel\Http\Requests\Brands;

use Illuminate\Foundation\Http\FormRequest;

class BrandMediaStoreRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'image', 'max:'.config('lunar.media.max_upload_kb', 8192)],
        ];
    }
}
