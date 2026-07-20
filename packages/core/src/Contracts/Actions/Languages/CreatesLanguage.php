<?php

namespace Lunar\Core\Contracts\Actions\Languages;

use Lunar\Core\Models\Language;

interface CreatesLanguage
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Language;
}
