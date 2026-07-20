<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Languages\DeletesLanguage;
use Lunar\Core\Contracts\Actions\Languages\UpdatesLanguage;
use Lunar\Core\Exceptions\LanguageActionException;
use Lunar\Core\Models\Language;
use Lunar\Panel\Http\Requests\Settings\LanguageRequest;

class LanguageEditController
{
    public function edit(Language $language): Response
    {
        return Inertia::render('settings/languages/Edit', [
            'language' => [
                'id' => $language->id,
                'code' => $language->code,
                'name' => $language->name,
                'default' => $language->default,
            ],
            'hasUrls' => $language->urls()->exists(),
            'urls' => [
                'update' => route('panel.settings.languages.update', $language),
                'destroy' => route('panel.settings.languages.destroy', $language),
                'index' => route('panel.settings.languages.index'),
            ],
        ]);
    }

    public function update(LanguageRequest $request, Language $language, UpdatesLanguage $updatesLanguage): RedirectResponse
    {
        try {
            $updatesLanguage->execute($language, $request->languageAttributes());
        } catch (LanguageActionException) {
            return back()->with('error', __('panel::languages.default_unset_blocked'));
        }

        return redirect()->route('panel.settings.languages.index')->with('success', __('panel::languages.flash_updated'));
    }

    public function destroy(Language $language, DeletesLanguage $deletesLanguage): RedirectResponse
    {
        try {
            $deletesLanguage->execute($language);
        } catch (LanguageActionException) {
            return back()->with('error', $language->default
                ? __('panel::languages.delete_blocked_default')
                : __('panel::languages.delete_blocked'));
        }

        return redirect()->route('panel.settings.languages.index')->with('success', __('panel::languages.flash_deleted'));
    }
}
