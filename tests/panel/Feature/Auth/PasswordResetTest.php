<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Notifications\ResetPassword;
use Lunar\Panel\Notifications\TwoFactorEmailCode;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

test('the forgot password screen renders', function () {
    $this->get(route('panel.password.request'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('auth/ForgotPassword'));
});

test('a reset link is sent to a known staff email', function () {
    Notification::fake();
    $staff = Staff::factory()->create();

    $this->post(route('panel.password.email'), ['email' => $staff->email])
        ->assertRedirect()
        ->assertSessionHas('success');

    Notification::assertSentTo($staff, ResetPassword::class);
});

test('an unknown email gets the same response and no notification', function () {
    Notification::fake();

    $this->post(route('panel.password.email'), ['email' => 'nobody@example.com'])
        ->assertRedirect()
        ->assertSessionHas('success');

    Notification::assertNothingSent();
});

test('the reset screen renders with email and token props', function () {
    $this->get(route('panel.password.reset', ['token' => 'fake-token', 'email' => 'staff@example.com']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/ResetPassword')
            ->where('email', 'staff@example.com')
            ->where('token', 'fake-token'));
});

test('a valid token resets the password', function () {
    Notification::fake();
    $staff = Staff::factory()->create();

    $this->post(route('panel.password.email'), ['email' => $staff->email]);

    $token = null;
    Notification::assertSentTo($staff, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    $this->post(route('panel.password.store'), [
        'token' => $token,
        'email' => $staff->email,
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])->assertRedirect(route('panel.login'));

    expect(Hash::check('new-secret-password', $staff->fresh()->password))->toBeTrue();

    $this->post(route('panel.login.store'), [
        'email' => $staff->email,
        'password' => 'new-secret-password',
    ])->assertRedirect(route('panel.two-factor.challenge'));

    $code = null;
    Notification::assertSentTo($staff, TwoFactorEmailCode::class, function (TwoFactorEmailCode $notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    $this->post(route('panel.two-factor.challenge.store'), ['code' => $code]);

    expect(Auth::guard('staff')->id())->toBe($staff->id);
});

test('an invalid token does not reset the password', function () {
    $staff = Staff::factory()->create();

    $this->post(route('panel.password.store'), [
        'token' => 'not-a-real-token',
        'email' => $staff->email,
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('password', $staff->fresh()->password))->toBeTrue();
});
