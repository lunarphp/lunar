<?php

namespace Lunar\Observers;

use Illuminate\Support\Facades\DB;
use Lunar\Models\Language;

class LanguageObserver
{
    /**
     * Handle the Language "created" event.
     *
     * @param  \Lunar\Models\Language  $language
     * @return void
     */
    public function created(Language $language)
    {
        $this->ensureOnlyOneDefault($language);
    }

    /**
     * Handle the Language "updated" event.
     *
     * @param  \Lunar\Models\Language  $language
     * @return void
     */
    public function updated(Language $language)
    {
        $this->ensureOnlyOneDefault($language);
    }

    /**
     * Handle the Language "deleted" event.
     *
     * @param  \Lunar\Models\Language  $language
     * @return void
     */
    public function deleting(Language $language)
    {
        DB::transaction(function () use ($language) {
            $language->urls()->delete();
        });
    }

    /**
     * Handle the Language "forceDeleted" event.
     *
     * @param  \Lunar\Models\Language  $language
     * @return void
     */
    public function forceDeleted(Language $language)
    {
        //
    }

    /**
     * Ensures that only one default language exists.
     *
     * @param  \Lunar\Models\Language  $savedLanguage  The language that was just saved.
     * @return void
     */
    protected function ensureOnlyOneDefault(Language $savedLanguage): void
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
