<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A single swatch image upload for a product option value. Accepts the same
 * image mime types as the standard media collection.
 */
class ProductOptionSwatchRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:'.config('lunar.media.max_upload_kb', 8192),
                'mimetypes:image/jpeg,image/png,image/gif,image/webp,image/avif,image/bmp,image/svg+xml',
            ],
        ];
    }
}
