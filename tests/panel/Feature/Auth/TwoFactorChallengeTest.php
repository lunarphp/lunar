<?php

use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Auth\AppAuthentication;
use Lunar\Tests\Panel\TestCase;
use PragmaRX\Google2FA\Google2FA;

uses(TestCase::class);

function currentOtp(string $secret = 'JBSWY3DPEHPK3PXP'): string
{
    return app(Google2FA::class)->getCurrentOtp($secret);
}

function submitCredentials(Staff $staff)
{
    return test()->post(route('panel.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
    ]);
}

test('login with two factor enabled diverts to the challenge without authenticating', function () {
    $staff = Staff::factory()->withTwoFactor()->create();

    submitCredentials($staff)->assertRedirect(route('panel.two-factor.challenge'));

    expect(Auth::guard('staff')->check())->toBeFalse()
        ->and(session('panel.login.id'))->toBe($staff->id);
});

test('the challenge screen renders when a login is pending', function () {
    $staff = Staff::factory()->withTwoFactor()->create();
    submitCredentials($staff);

    $this->get(route('panel.two-factor.challenge'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('auth/TwoFactorChallenge'));
});

test('the challenge screen redirects to login when nothing is pending', function () {
    $this->get(route('panel.two-factor.challenge'))
        ->assertRedirect(route('panel.login'));
});

test('the challenge submit redirects to login when nothing is pending', function () {
    $this->post(route('panel.two-factor.challenge.store'), ['code' => '123456'])
        ->assertRedirect(route('panel.login'));
});

test('a valid totp code completes the login', function () {
    $staff = Staff::factory()->withTwoFactor()->create();
    submitCredentials($staff);

    $this->post(route('panel.two-factor.challenge.store'), ['code' => currentOtp()])
        ->assertRedirect(route('panel.dashboard'));

    expect(Auth::guard('staff')->id())->toBe($staff->id)
        ->and(session()->has('panel.login.id'))->toBeFalse();
});

test('an invalid code is rejected', function () {
    $staff = Staff::factory()->withTwoFactor()->create();
    submitCredentials($staff);

    $this->post(route('panel.two-factor.challenge.store'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    expect(Auth::guard('staff')->check())->toBeFalse();
});

test('a totp code cannot be replayed', function () {
    $staff = Staff::factory()->withTwoFactor()->create();
    $code = currentOtp();

    submitCredentials($staff);
    $this->post(route('panel.two-factor.challenge.store'), ['code' => $code]);
    $this->post(route('panel.logout'));

    submitCredentials($staff);
    $this->post(route('panel.two-factor.challenge.store'), ['code' => $code])
        ->assertSessionHasErrors('code');

    expect(Auth::guard('staff')->check())->toBeFalse();
});

test('a recovery code completes the login and is consumed', function () {
    $appAuth = app(AppAuthentication::class);
    $staff = Staff::factory()->create([
        'app_authentication_secret' => 'JBSWY3DPEHPK3PXP',
        'app_authentication_recovery_codes' => $appAuth->hashRecoveryCodes(['known-code-1', 'known-code-2']),
    ]);

    submitCredentials($staff);
    $this->post(route('panel.two-factor.challenge.store'), ['recovery_code' => 'known-code-1'])
        ->assertRedirect(route('panel.dashboard'));

    expect(Auth::guard('staff')->id())->toBe($staff->id)
        ->and($staff->fresh()->app_authentication_recovery_codes)->toHaveCount(1);

    $this->post(route('panel.logout'));

    submitCredentials($staff);
    $this->post(route('panel.two-factor.challenge.store'), ['recovery_code' => 'known-code-1'])
        ->assertSessionHasErrors('recovery_code');
});

test('the challenge is rate limited after five failed attempts', function () {
    $staff = Staff::factory()->withTwoFactor()->create();
    submitCredentials($staff);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('panel.two-factor.challenge.store'), ['code' => '000000']);
    }

    $this->post(route('panel.two-factor.challenge.store'), ['code' => currentOtp()])
        ->assertSessionHasErrors('code');

    expect(Auth::guard('staff')->check())->toBeFalse();
});

test('the remember flag survives the challenge', function () {
    $staff = Staff::factory()->withTwoFactor()->create();

    $this->post(route('panel.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
        'remember' => true,
    ]);

    $this->post(route('panel.two-factor.challenge.store'), ['code' => currentOtp()])
        ->assertCookie(Auth::guard('staff')->getRecallerName());
});
