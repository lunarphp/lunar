<?php

namespace Lunar\Core\Actions\Languages;

use Lunar\Core\Contracts\Actions\Languages\DeletesLanguage;
use Lunar\Core\Exceptions\LanguageActionException;
use Lunar\Core\Models\Language;

/**
 * Delete a language. Languages with URLs are kept — their URLs reference the
 * language and would be orphaned. The default language is also kept: make
 * another language the default first.
 */
class DeleteLanguage implements DeletesLanguage
{
    public function execute(Language $language): void
    {
        if ($language->default) {
            throw new LanguageActionException('Cannot delete the default language. Make another language the default first.');
        }

        if ($language->urls()->exists()) {
            throw new LanguageActionException('Cannot delete a language with URLs.');
        }

        $language->delete();
    }
}
