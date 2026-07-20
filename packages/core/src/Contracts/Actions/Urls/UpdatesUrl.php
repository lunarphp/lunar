<?php

namespace Lunar\Core\Contracts\Actions\Urls;

use Lunar\Core\Models\Url;

interface UpdatesUrl
{
    /**
     * @param  array{language_id?: int, slug?: string, default?: bool}  $attributes
     */
    public function execute(Url $url, array $attributes): Url;
}
