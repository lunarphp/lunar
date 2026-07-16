<?php

namespace Lunar\Panel\Http\Controllers\Account;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Panel\Auth\AppAuthentication;
use Lunar\Panel\PanelManager;

class SecurityController
{
    public function edit(
        Request $request,
        PanelManager $manager,
        AppAuthentication $appAuthentication,
    ): Response {
        $staff = $request->user($manager->guard());
        $pendingSecret = $request->session()->get('panel.two_factor.pending_secret');

        return Inertia::render('account/Security', [
            'twoFactorEnabled' => $appAuthentication->isEnabled($staff),
            'pendingTwoFactor' => $pendingSecret ? [
                'secret' => $pendingSecret,
                'qrCode' => $appAuthentication->qrCodeDataUri($manager->name(), $staff->email, $pendingSecret),
            ] : null,
            'recoveryCodes' => $request->session()->get('panel.two_factor.recovery_codes'),
            'urls' => [
                'password' => route('panel.account.password.update'),
                'twoFactor' => route('panel.account.two-factor.store'),
                'twoFactorConfirm' => route('panel.account.two-factor.confirm'),
                'twoFactorDisable' => route('panel.account.two-factor.destroy'),
                'recoveryCodes' => route('panel.account.two-factor.recovery-codes'),
            ],
        ]);
    }
}
