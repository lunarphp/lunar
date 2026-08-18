<?php

namespace Lunar\Panel\Http\Controllers\Account;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Lunar\Panel\Auth\AppAuthentication;
use Lunar\Panel\PanelManager;

class ConfirmTwoFactorController
{
    public function store(
        Request $request,
        PanelManager $manager,
        AppAuthentication $appAuthentication,
    ): RedirectResponse {
        $request->validate(['code' => ['required', 'string']]);

        $staff = $request->user($manager->guard());
        $secret = $request->session()->get('panel.two_factor.pending_secret');

        if (! $secret) {
            return redirect()->route('panel.account.security');
        }

        $throttleKey = 'lunar-panel:two-factor-setup:'.$staff->getAuthIdentifier();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'code' => __('panel::auth.throttle', [
                    'seconds' => RateLimiter::availableIn($throttleKey),
                ]),
            ]);
        }

        if (! $appAuthentication->verifyCode($secret, $request->string('code')->value(), preventReuse: true)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'code' => __('panel::auth.invalid_code'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        $recoveryCodes = $appAuthentication->generateRecoveryCodes();

        $staff->forceFill([
            'app_authentication_secret' => $secret,
            'app_authentication_recovery_codes' => $appAuthentication->hashRecoveryCodes($recoveryCodes),
        ])->save();

        $request->session()->forget('panel.two_factor.pending_secret');
        $request->session()->flash('panel.two_factor.recovery_codes', $recoveryCodes);

        return back()->with('success', __('panel::auth.two_factor_confirmed'));
    }
}
