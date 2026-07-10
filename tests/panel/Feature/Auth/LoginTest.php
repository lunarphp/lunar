<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Notifications\TwoFactorEmailCode;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

test('the login screen renders', function () {
    $this->get(route('panel.login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Login')
            ->has('urls.store')
            ->has('locale'));
});

test('staff without two factor configured are challenged by email rather than logged in immediately', function () {
    Notification::fake();
    $staff = Staff::factory()->create();

    $this->post(route('panel.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
    ])->assertRedirect(route('panel.two-factor.challenge'));

    expect(Auth::guard('staff')->check())->toBeFalse();

    $code = null;
    Notification::assertSentTo($staff, TwoFactorEmailCode::class, function (TwoFactorEmailCode $notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    $this->post(route('panel.two-factor.challenge.store'), ['code' => $code])
        ->assertRedirect(route('panel.dashboard'));

    expect(Auth::guard('staff')->id())->toBe($staff->id);
});

test('staff cannot authenticate with an invalid password', function () {
    $staff = Staff::factory()->create();

    $this->from(route('panel.login'))->post(route('panel.login.store'), [
        'email' => $staff->email,
        'password' => 'wrong-password',
    ])->assertRedirect(route('panel.login'))
        ->assertSessionHasErrors('email');

    expect(Auth::guard('staff')->check())->toBeFalse();
});

test('login is rate limited after five failed attempts', function () {
    $staff = Staff::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('panel.login.store'), [
            'email' => $staff->email,
            'password' => 'wrong-password',
        ]);
    }

    $this->post(route('panel.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    expect(Auth::guard('staff')->check())->toBeFalse();
});

test('the remember flag issues a recaller cookie', function () {
    Notification::fake();
    $staff = Staff::factory()->create();

    $this->post(route('panel.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
        'remember' => true,
    ]);

    $code = null;
    Notification::assertSentTo($staff, TwoFactorEmailCode::class, function (TwoFactorEmailCode $notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    $this->post(route('panel.two-factor.challenge.store'), ['code' => $code])
        ->assertCookie(Auth::guard('staff')->getRecallerName());
});

test('authenticated staff are redirected away from the login screen', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get(route('panel.login'))
        ->assertRedirect(route('panel.dashboard'));
});

test('staff can log out', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->post(route('panel.logout'))
        ->assertRedirect(route('panel.login'));

    expect(Auth::guard('staff')->check())->toBeFalse();
});
