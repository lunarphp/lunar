<?php

namespace Lunar\Core\Actions\Urls;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Urls\CreatesUrl;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Url;

/**
 * Create a URL for a HasUrls element, keeping exactly one default per
 * element: the element's first URL is always the default, and creating a
 * new default demotes the current one.
 */
class CreateUrl implements CreatesUrl
{
    public function execute(Model $element, array $attributes): Url
    {
        return DB::transaction(function () use ($element, $attributes): Url {
            $hasUrls = $element->urls()->exists();

            $attributes['default'] = ! $hasUrls || (bool) ($attributes['default'] ?? false);

            if ($attributes['default'] && $hasUrls) {
                $element->urls()->update(['default' => false]);
            }

            return $element->urls()->create($attributes);
        });
    }
}
