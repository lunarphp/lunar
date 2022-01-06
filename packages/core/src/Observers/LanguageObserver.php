<?php

namespace GetCandy\Observers;

use GetCandy\Models\Language;

class LanguageObserver
{
    /**
     * Handle the Language "created" event.
     *
     * @param \GetCandy\Models\Language $language
     *
     * @return void
     */
    public function created(Language $language)
    {
        $this->ensureOnlyOneDefault($language);
    }

    /**
     * Handle the Language "updated" event.
     *
     * @param \GetCandy\Models\Language $language
     *
     * @return void
     */
    public function updated(Language $language)
    {
        $this->ensureOnlyOneDefault($language);
    }

    /**
     * Handle the Language "deleted" event.
     *
     * @param \GetCandy\Models\Language $language
     *
     * @return void
     */
    public function deleted(Language $language)
    {
        //
    }

    /**
     * Handle the Language "forceDeleted" event.
     *
     * @param \GetCandy\Models\Language $language
     *
     * @return void
     */
    public function forceDeleted(Language $language)
    {
        //
    }

    /**
     * Ensures that only one default language exists.
     *
     * @param \GetCandy\Models\Language $savedLanguage The language that was just saved.
     *
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
