<?php

namespace Lunar\Core\Contracts\Actions\Urls;

use Lunar\Core\Models\Url;

interface DeletesUrl
{
    public function execute(Url $url): void;
}
