<?php

namespace Lunar\Hub\Http\Livewire\Traits;

use Lunar\Models\Language;

trait WithLanguages
{
    /**
     * Getter for default language.
     *
     * @return \Lunar\Models\Language
     */
    public function getDefaultLanguageProperty()
    {
        return $this->languages->first(fn ($language) => $language->default);
    }

    /**
     * Getter for all languages in the system.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getLanguagesProperty()
    {
        return Language::get();
    }
}
