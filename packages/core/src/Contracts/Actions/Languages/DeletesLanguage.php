<?php

namespace Lunar\Core\Contracts\Actions\Languages;

use Lunar\Core\Models\Language;

interface DeletesLanguage
{
    public function execute(Language $language): void;
}
