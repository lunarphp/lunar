<?php

namespace Lunar\Observers;

use Lunar\Facades\DB;
use Lunar\Models\Contracts\Language as LanguageContract;
use Lunar\Models\Language;

class LanguageObserver
{
    /**
     * Handle the Language "created" event.
     *
     * @return void
     */
    public function created(LanguageContract $language)
    {
        $this->ensureOnlyOneDefault($language);
    }

    /**
     * Handle the Language "updated" event.
     *
     * @return void
     */
    public function updated(LanguageContract $language)
    {
        $this->ensureOnlyOneDefault($language);
    }

    /**
     * Handle the Language "deleted" event.
     *
     * @return void
     */
    public function deleting(LanguageContract $language)
    {
        DB::transaction(function () use ($language) {
            $language->urls()->delete();
        });
    }

    /**
     * Handle the Language "forceDeleted" event.
     *
     * @return void
     */
    public function forceDeleted(LanguageContract $language)
    {
        //
    }

    /**
     * Ensures that only one default language exists.
     *
     * @param  LanguageContract  $savedLanguage  The language that was just saved.
     */
    protected function ensureOnlyOneDefault(LanguageContract $savedLanguage): void
    {
        // Wrap here so we avoid a query if it's not been set to default.
        if ($savedLanguage->default) {
            Language::withoutEvents(function () use ($savedLanguage) {
                Language::whereDefault(true)->where('id', '!=', $savedLanguage->id)->update([
                    'default' => false,
                ]);
            });
        }
    }
}
