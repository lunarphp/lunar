<?php

namespace Lunar\Panel\Http\Controllers\Account;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lunar\Panel\PanelManager;

class LocaleController
{
    public function __construct(protected PanelManager $manager) {}

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in($this->manager->availableLocales())],
        ]);

        $request->user($this->manager->guard())
            ->forceFill(['preferred_locale' => $validated['locale']])
            ->save();

        return back();
    }
}
