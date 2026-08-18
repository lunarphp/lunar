<?php

namespace Lunar\Panel\Http\Controllers\Account;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lunar\Panel\Auth\AppAuthentication;
use Lunar\Panel\PanelManager;

class RecoveryCodesController
{
    public function store(
        Request $request,
        PanelManager $manager,
        AppAuthentication $appAuthentication,
    ): RedirectResponse {
        $request->validate([
            'password' => ['required', 'current_password:'.$manager->guard()],
        ]);

        $staff = $request->user($manager->guard());

        if (! $appAuthentication->isEnabled($staff)) {
            return back();
        }

        $recoveryCodes = $appAuthentication->generateRecoveryCodes();

        $staff->forceFill([
            'app_authentication_recovery_codes' => $appAuthentication->hashRecoveryCodes($recoveryCodes),
        ])->save();

        $request->session()->flash('panel.two_factor.recovery_codes', $recoveryCodes);

        return back()->with('success', __('panel::auth.recovery_codes_regenerated'));
    }
}
