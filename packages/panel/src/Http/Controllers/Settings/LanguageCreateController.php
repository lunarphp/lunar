<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Languages\CreatesLanguage;
use Lunar\Panel\Http\Requests\Settings\LanguageRequest;

class LanguageCreateController
{
    public function store(LanguageRequest $request, CreatesLanguage $createsLanguage): RedirectResponse
    {
        $createsLanguage->execute($request->languageAttributes());

        return redirect()->route('panel.settings.languages.index')->with('success', __('panel::languages.flash_created'));
    }
}
