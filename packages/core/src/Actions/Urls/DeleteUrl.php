<?php

namespace Lunar\Core\Actions\Urls;

use Lunar\Core\Contracts\Actions\Urls\DeletesUrl;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Url;

/**
 * Delete a URL. When the deleted URL was its language's default, the first
 * remaining URL in that language is promoted so the element keeps exactly
 * one default per language.
 */
class DeleteUrl implements DeletesUrl
{
    public function execute(Url $url): void
    {
        DB::transaction(function () use ($url): void {
            $wasDefault = $url->default;
            $element = $url->element;

            $url->delete();

            if ($wasDefault) {
                $element->urls()
                    ->where('language_id', $url->language_id)
                    ->orderBy('id')
                    ->first()
                    ?->update(['default' => true]);
            }
        });
    }
}
