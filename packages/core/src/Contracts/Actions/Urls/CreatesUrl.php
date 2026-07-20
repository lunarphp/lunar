<?php

namespace Lunar\Core\Contracts\Actions\Urls;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Concerns\HasUrls;
use Lunar\Core\Models\Url;

interface CreatesUrl
{
    /**
     * @param  Model&HasUrls  $element
     * @param  array{language_id: int, slug: string, default?: bool}  $attributes
     */
    public function execute(Model $element, array $attributes): Url;
}
