<?php

namespace Lunar\Core\Actions\Languages;

use Lunar\Core\Contracts\Actions\Languages\CreatesLanguage;
use Lunar\Core\Models\Language;

/**
 * Create a language, ensuring at most one language is ever marked default.
 */
class CreateLanguage implements CreatesLanguage
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Language
    {
        if ($attributes['default'] ?? false) {
            Language::query()->where('default', true)->update(['default' => false]);
        }

        return Language::create($attributes);
    }
}
