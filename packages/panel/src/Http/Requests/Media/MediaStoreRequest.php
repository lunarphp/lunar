<?php

namespace Lunar\Panel\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;

/**
 * Shared media upload validation. The target collection travels in the request
 * body and is validated against the model's registered collections, so a
 * request can only ever write to a collection the model actually declares. The
 * accepted file types are read from that collection's own mime-type allowlist.
 */
abstract class MediaStoreRequest extends FormRequest
{
    abstract protected function mediaModel(): HasMedia;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $collections = $this->mediaModel()->getRegisteredMediaCollections();
        $collection = $collections->firstWhere('name', $this->resolvedCollection());

        $fileRules = ['file', 'max:'.config('lunar.media.max_upload_kb', 8192)];

        if ($collection instanceof MediaCollection && $collection->acceptsMimeTypes !== []) {
            $fileRules[] = 'mimetypes:'.implode(',', $collection->acceptsMimeTypes);
        }

        return [
            'collection' => ['nullable', 'string', Rule::in($collections->pluck('name'))],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => $fileRules,
        ];
    }

    public function resolvedCollection(): string
    {
        return $this->input('collection') ?: config('lunar.media.collection');
    }
}
