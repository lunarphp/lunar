<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Notifications\TwoFactorEmailCode;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

function submitCredentialsWithoutTwoFactor(Staff $staff)
{
    return test()->post(route('panel.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
    ]);
}

function emailedCode(Staff $staff): string
{
    $code = null;

    Notification::assertSentTo($staff, TwoFactorEmailCode::class, function (TwoFactorEmailCode $notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    return $code;
}

test('login without two factor configured diverts to an email challenge instead of authenticating', function () {
    Notification::fake();
    $staff = Staff::factory()->create(['email' => 'jane@example.com']);

    submitCredentialsWithoutTwoFactor($staff)->assertRedirect(route('panel.two-factor.challenge'));

    expect(Auth::guard('staff')->check())->toBeFalse()
        ->and(session('panel.login.id'))->toBe($staff->id);

    Notification::assertSentTo($staff, TwoFactorEmailCode::class);
});

test('the challenge screen exposes email method with an obfuscated address and cooldown', function () {
    Notification::fake();
    $staff = Staff::factory()->create(['email' => 'jane@example.com']);
    submitCredentialsWithoutTwoFactor($staff);

    $this->get(route('panel.two-factor.challenge'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/TwoFactorChallenge')
            ->where('method', 'email')
            ->where('obfuscatedEmail', 'j•••@example.com')
            ->where('cooldownRemaining', 30));
});

test('a correct emailed code completes the login and cannot be reused', function () {
    Notification::fake();
    $staff = Staff::factory()->create();
    submitCredentialsWithoutTwoFactor($staff);
    $code = emailedCode($staff);

    $this->post(route('panel.two-factor.challenge.store'), ['code' => $code])
        ->assertRedirect(route('panel.dashboard'));

    expect(Auth::guard('staff')->id())->toBe($staff->id)
        ->and(session()->has('panel.login.id'))->toBeFalse();

    $this->post(route('panel.logout'));

    submitCredentialsWithoutTwoFactor($staff);
    $this->post(route('panel.two-factor.challenge.store'), ['code' => $code])
        ->assertSessionHasErrors('code');

    expect(Auth::guard('staff')->check())->toBeFalse();
});

test('a wrong emailed code is rejected and does not authenticate', function () {
    Notification::fake();
    $staff = Staff::factory()->create();
    submitCredentialsWithoutTwoFactor($staff);
    emailedCode($staff);

    $this->post(route('panel.two-factor.challenge.store'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    expect(Auth::guard('staff')->check())->toBeFalse();
});

test('recovery codes are not accepted in email challenge mode', function () {
    Notification::fake();
    $staff = Staff::factory()->create();
    submitCredentialsWithoutTwoFactor($staff);
    emailedCode($staff);

    $this->post(route('panel.two-factor.challenge.store'), ['recovery_code' => 'anything-at-all'])
        ->assertSessionHasErrors('recovery_code');

    expect(Auth::guard('staff')->check())->toBeFalse();
});

test('the email challenge is rate limited after five failed attempts', function () {
    Notification::fake();
    $staff = Staff::factory()->create();
    submitCredentialsWithoutTwoFactor($staff);
    $code = emailedCode($staff);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('panel.two-factor.challenge.store'), ['code' => '000000']);
    }

    $this->post(route('panel.two-factor.challenge.store'), ['code' => $code])
        ->assertSessionHasErrors('code');

    expect(Auth::guard('staff')->check())->toBeFalse();
});

test('the remember flag survives the email challenge', function () {
    Notification::fake();
    $staff = Staff::factory()->create();

    $this->post(route('panel.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
        'remember' => true,
    ]);
    $code = emailedCode($staff);

    $this->post(route('panel.two-factor.challenge.store'), ['code' => $code])
        ->assertCookie(Auth::guard('staff')->getRecallerName());
});

test('resend is rejected while cooling down and surfaces the remaining seconds', function () {
    Notification::fake();
    $staff = Staff::factory()->create();
    submitCredentialsWithoutTwoFactor($staff);

    $this->post(route('panel.two-factor.challenge.resend'))
        ->assertSessionHasErrors('code');

    Notification::assertSentToTimes($staff, TwoFactorEmailCode::class, 1);
});

test('resend succeeds and sends a new code once the cooldown has elapsed', function () {
    Notification::fake();
    $staff = Staff::factory()->create();
    submitCredentialsWithoutTwoFactor($staff);

    $this->travel(31)->seconds();

    $this->post(route('panel.two-factor.challenge.resend'))
        ->assertRedirect(route('panel.two-factor.challenge'))
        ->assertSessionHas('success');

    Notification::assertSentToTimes($staff, TwoFactorEmailCode::class, 2);
});

test('resend redirects to login when nothing is pending', function () {
    $this->post(route('panel.two-factor.challenge.resend'))
        ->assertRedirect(route('panel.login'));
});
