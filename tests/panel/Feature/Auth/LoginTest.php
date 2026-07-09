<?php

use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

test('the login screen renders', function () {
    $this->get(route('panel.login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Login')
            ->has('urls.store')
            ->has('lang.auth.sign_in_title'));
});

test('staff can authenticate with valid credentials', function () {
    $staff = Staff::factory()->create();

    $this->post(route('panel.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
    ])->assertRedirect(route('panel.dashboard'));

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
    $staff = Staff::factory()->create();

    $this->post(route('panel.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
        'remember' => true,
    ])->assertCookie(Auth::guard('staff')->getRecallerName());
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
