<?php

namespace Lunar\Core\Actions\Languages;

use Lunar\Core\Contracts\Actions\Languages\UpdatesLanguage;
use Lunar\Core\Exceptions\LanguageActionException;
use Lunar\Core\Models\Language;

/**
 * Update a language, ensuring at most one language is ever marked default.
 * The default flag moves by promoting another language, never by unsetting —
 * so a store with languages always has a default.
 */
class UpdateLanguage implements UpdatesLanguage
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Language $language, array $attributes): Language
    {
        if ($language->default && array_key_exists('default', $attributes) && ! $attributes['default']) {
            throw new LanguageActionException('Cannot unset the default language. Make another language the default instead.');
        }

        if ($attributes['default'] ?? false) {
            Language::query()->where('default', true)->where('id', '!=', $language->id)->update(['default' => false]);
        }

        $language->update($attributes);

        return $language;
    }
}
