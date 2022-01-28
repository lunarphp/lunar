<?php

namespace GetCandy\Observers;

use GetCandy\Models\Url;

class UrlObserver
{
    /**
     * Handle the Url "created" event.
     *
     * @param  \GetCandy\Models\Url  $url
     * @return void
     */
    public function created(Url $url)
    {
        $this->ensureOnlyOneDefault($url);
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \GetCandy\Models\Url  $url
     * @return void
     */
    public function updated(Url $url)
    {
        $this->ensureOnlyOneDefault($url);
    }

    /**
     * Handle the Url "deleted" event.
     *
     * @param  \GetCandy\Models\Url  $url
     * @return void
     */
    public function deleted(Url $url)
    {
        if ($url->default) {
            $url = Url::whereDefault(false)
                ->where('id', '!=', $url->id)
                ->whereElementType($url->element_type)
                ->whereElementId($url->element_id)
                ->whereLanguageId($url->language_id)
                ->first();

            if ($url) {
                $url->default = true;
                $url->saveQuietly();
            }
        }
    }

    /**
     * Ensures that only one default channel exists.
     *
     * @param  Url  $savedUrl  The url that was just saved.
     * @return void
     */
    protected function ensureOnlyOneDefault(Url $savedUrl): void
    {
        // Wrap here so we avoid a query if it's not been set to default.
        if ($savedUrl->default) {
            $url = Url::whereDefault(true)
                ->where('id', '!=', $savedUrl->id)
                ->whereElementType($savedUrl->element_type)
                ->whereElementId($savedUrl->element_id)
                ->whereLanguageId($savedUrl->language_id)
                ->first();

            if ($url) {
                $url->default = false;
                $url->saveQuietly();
            }
        }
    }
}
