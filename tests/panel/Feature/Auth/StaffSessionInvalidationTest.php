<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Notifications\TwoFactorEmailCode;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

/*
 * A staff member who changes or resets their password after a suspected
 * compromise must not leave another browser signed in to the panel.
 */
beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
});

test('a panel session ends once the staff password changes elsewhere', function () {
    $this->actingAs($this->staff, 'staff')
        ->get(route('panel.dashboard'))
        ->assertOk()
        ->assertSessionHas('password_hash_staff');

    // Another device (or a reset) changes the password.
    $this->staff->forceFill(['password' => 'changed-elsewhere-password'])->save();

    $this->get(route('panel.dashboard'))->assertRedirect(route('panel.login'));

    $this->assertGuest('staff');
});

test('changing the password keeps the session that changed it', function () {
    $this->actingAs($this->staff, 'staff')->get(route('panel.dashboard'))->assertOk();

    $this->put(route('panel.account.password.update'), [
        'current_password' => 'password',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])->assertRedirect()->assertSessionHas('success');

    $this->get(route('panel.dashboard'))->assertOk();
    $this->assertAuthenticatedAs($this->staff->fresh(), 'staff');
});

test('changing the password rotates the remember token', function () {
    $this->staff->forceFill(['remember_token' => 'old-remember-token'])->save();

    $this->actingAs($this->staff, 'staff')->put(route('panel.account.password.update'), [
        'current_password' => 'password',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])->assertSessionHas('success');

    $staff = $this->staff->fresh();

    expect($staff->remember_token)->not->toBe('old-remember-token')
        ->and(Hash::check('new-secret-password', $staff->password))->toBeTrue();
});

test('the storefront password hash is left alone', function () {
    $this->withSession(['password_hash_web' => 'storefront-hash'])
        ->actingAs($this->staff, 'staff')
        ->get(route('panel.dashboard'))
        ->assertOk()
        ->assertSessionHas('password_hash_web', 'storefront-hash')
        ->assertSessionHas('password_hash_staff');
});

test('changing the password keeps remember-me on this browser with the new token', function () {
    $recaller = Auth::guard('staff')->getRecallerName();
    $hash = Auth::guard('staff')->hashPasswordForCookie($this->staff->password);

    $response = $this->actingAs($this->staff, 'staff')
        ->withCookie($recaller, $this->staff->id.'|old-remember-token|'.$hash)
        ->put(route('panel.account.password.update'), [
            'current_password' => 'password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
        ->assertSessionHas('success')
        ->assertCookie($recaller);

    $token = $this->staff->fresh()->remember_token;

    expect($token)->not->toBe('old-remember-token')
        ->and(explode('|', $response->getCookie($recaller)->getValue())[1] ?? null)->toBe($token);
});

test('a new sign-in does not inherit an earlier staff member\'s stored hash', function () {
    // The guard's login() writes the signing-in member's hash over any left
    // in the session, so a stale one never ends the new session.
    Notification::fake();

    $this->withSession(['password_hash_staff' => 'left-by-someone-else']);

    $this->post(route('panel.login.store'), ['email' => $this->staff->email, 'password' => 'password'])
        ->assertRedirect(route('panel.two-factor.challenge'));

    $code = null;
    Notification::assertSentTo($this->staff, TwoFactorEmailCode::class, function (TwoFactorEmailCode $notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    $this->post(route('panel.two-factor.challenge.store'), ['code' => $code])
        ->assertRedirect(route('panel.dashboard'));

    $this->get(route('panel.dashboard'))->assertOk();
});
