<?php

namespace Lunar\Panel\Http\Controllers\Account;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Lunar\Panel\PanelManager;

class PasswordController
{
    public function update(Request $request, PanelManager $manager): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:'.$manager->guard()],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $request->user($manager->guard())->forceFill([
            'password' => $validated['password'],
        ])->save();

        return back()->with('success', __('panel::auth.password_updated'));
    }
}
