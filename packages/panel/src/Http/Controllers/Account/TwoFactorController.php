<?php

namespace Lunar\Panel\Http\Controllers\Account;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lunar\Panel\Auth\AppAuthentication;
use Lunar\Panel\PanelManager;

class TwoFactorController
{
    /**
     * Begin enrolment. The secret stays in the session until confirmed —
     * a persisted secret would read as "2FA enabled" to the Filament admin.
     */
    public function store(
        Request $request,
        PanelManager $manager,
        AppAuthentication $appAuthentication,
    ): RedirectResponse {
        if ($appAuthentication->isEnabled($request->user($manager->guard()))) {
            return back();
        }

        $request->session()->put(
            'panel.two_factor.pending_secret',
            $appAuthentication->generateSecret(),
        );

        return back();
    }

    public function destroy(Request $request, PanelManager $manager): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password:'.$manager->guard()],
        ]);

        $request->user($manager->guard())->forceFill([
            'app_authentication_secret' => null,
            'app_authentication_recovery_codes' => null,
        ])->save();

        return back()->with('success', __('panel::auth.two_factor_disabled'));
    }
}
