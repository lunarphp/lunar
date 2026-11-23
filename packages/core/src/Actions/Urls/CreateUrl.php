<?php

namespace Lunar\Core\Actions\Urls;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Urls\CreatesUrl;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Url;

/**
 * Create a URL for a HasUrls element, keeping exactly one default per
 * element and language: the element's first URL in a language is always
 * that language's default, and creating a new default demotes the current
 * one in the same language.
 */
class CreateUrl implements CreatesUrl
{
    public function execute(Model $element, array $attributes): Url
    {
        return DB::transaction(function () use ($element, $attributes): Url {
            $siblings = $element->urls()->where('language_id', $attributes['language_id'] ?? null);
            $hasUrls = $siblings->exists();

            $attributes['default'] = ! $hasUrls || (bool) ($attributes['default'] ?? false);

            if ($attributes['default'] && $hasUrls) {
                $siblings->update(['default' => false]);
            }

            return $element->urls()->create($attributes);
        });
    }
}
