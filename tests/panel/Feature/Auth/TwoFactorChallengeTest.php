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
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/TwoFactorChallenge')
            ->where('method', 'authenticator'));
});

test('the challenge screen redirects to login when nothing is pending', function () {
    $this->get(route('panel.two-factor.challenge'))
        ->assertRedirect(route('panel.login'));
});

test('a guest visiting a panel page goes to login when nothing is pending', function () {
    $this->get(route('panel.products.index'))
        ->assertRedirect(route('panel.login'));
});

test('a panel page sends a pending login back to the challenge and then on to that page', function () {
    $staff = Staff::factory()->withTwoFactor()->create(['admin' => true]);
    submitCredentials($staff);

    $this->get(route('panel.products.index'))
        ->assertRedirect(route('panel.two-factor.challenge'));

    $this->post(route('panel.two-factor.challenge.store'), ['code' => currentOtp()])
        ->assertRedirect(route('panel.products.index'));
});

test('signing in lands on the dashboard when the intended page is one the staff member cannot open', function () {
    $staff = Staff::factory()->withTwoFactor()->create(['admin' => false]);

    $this->get(route('panel.settings.channels.index'))
        ->assertRedirect(route('panel.login'));

    submitCredentials($staff);

    $this->post(route('panel.two-factor.challenge.store'), ['code' => currentOtp()])
        ->assertRedirect(route('panel.dashboard'));

    expect(session('url.intended'))->toBeNull();
});

test('signing in still goes to an intended page the staff member is permitted to open', function () {
    $staff = Staff::factory()->withTwoFactor()->create(['admin' => false]);
    $staff->givePermissionTo('settings:core');

    $this->get(route('panel.settings.channels.index'))
        ->assertRedirect(route('panel.login'));

    submitCredentials($staff);

    $this->post(route('panel.two-factor.challenge.store'), ['code' => currentOtp()])
        ->assertRedirect(route('panel.settings.channels.index'));
});

test('the login screen stays reachable while a login is pending', function () {
    $staff = Staff::factory()->withTwoFactor()->create();
    submitCredentials($staff);

    // "Use a different account" on the challenge links here.
    $this->get(route('panel.login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('auth/Login'));
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
