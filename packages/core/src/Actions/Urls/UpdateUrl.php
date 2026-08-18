<?php

namespace Lunar\Core\Actions\Urls;

use Lunar\Core\Contracts\Actions\Urls\UpdatesUrl;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Url;

/**
 * Update a URL. Promoting a URL to default demotes its siblings; the default
 * cannot be unset directly — promote another URL instead.
 */
class UpdateUrl implements UpdatesUrl
{
    public function execute(Url $url, array $attributes): Url
    {
        return DB::transaction(function () use ($url, $attributes): Url {
            if ($url->default) {
                unset($attributes['default']);
            }

            if ($attributes['default'] ?? false) {
                $url->element->urls()->whereKeyNot($url->getKey())->update(['default' => false]);
            }

            $url->update($attributes);

            return $url;
        });
    }
}
