<?php

namespace Lunar\Panel\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Shared media metadata validation. Alt text is only required for images; other
 * collections (e.g. document downloads) edit just a display name and caption.
 */
class MediaUpdateRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'alt' => [$this->isImageMedia() ? 'required' : 'nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'focal' => ['nullable', 'array:x,y'],
            'focal.x' => ['required_with:focal', 'integer', 'between:0,100'],
            'focal.y' => ['required_with:focal', 'integer', 'between:0,100'],
            'primary' => ['nullable', 'boolean'],
        ];
    }

    protected function isImageMedia(): bool
    {
        $media = $this->route('media');

        return $media instanceof Media
            && $media->collection_name === config('lunar.media.collection');
    }
}
