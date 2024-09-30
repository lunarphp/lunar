<?php

namespace Lunar\Observers;

use Lunar\Models\Contracts\Url as UrlContract;
use Lunar\Models\Url;

class UrlObserver
{
    /**
     * Handle the Url "created" event.
     *
     * @return void
     */
    public function created(UrlContract $url)
    {
        $this->ensureOnlyOneDefault($url);
    }

    /**
     * Handle the User "updated" event.
     *
     * @return void
     */
    public function updated(UrlContract $url)
    {
        $this->ensureOnlyOneDefault($url);
    }

    /**
     * Handle the Url "deleted" event.
     *
     * @return void
     */
    public function deleted(UrlContract $url)
    {
        /** @var Url $url */
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
     */
    protected function ensureOnlyOneDefault(UrlContract $savedUrl): void
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
