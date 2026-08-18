<?php

namespace Lunar\Core\Contracts\Actions\Languages;

use Lunar\Core\Models\Language;

interface UpdatesLanguage
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Language $language, array $attributes): Language;
}
