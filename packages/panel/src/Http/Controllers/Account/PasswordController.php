<?php

namespace Lunar\Panel\Http\Controllers\Account;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
            'remember_token' => Str::random(60),
        ])->save();

        // Ends the staff member's other panel sessions (checked by
        // AuthenticateSession on their next request) and keeps this one.
        Auth::guard($manager->guard())->logoutOtherDevices($validated['password']);

        return back()->with('success', __('panel::auth.password_updated'));
    }
}
