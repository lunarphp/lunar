<?php

use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;
use PragmaRX\Google2FA\Google2FA;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the security screen renders with two factor state', function () {
    $this->get(route('panel.account.security'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/Security')
            ->where('twoFactorEnabled', false)
            ->where('pendingTwoFactor', null)
            ->where('recoveryCodes', null));
});

test('beginning enrolment stores a pending secret in the session only', function () {
    $this->post(route('panel.account.two-factor.store'))->assertRedirect();

    expect(session('panel.two_factor.pending_secret'))->toMatch('/^[A-Z2-7]{16}$/')
        ->and($this->staff->fresh()->app_authentication_secret)->toBeNull();

    $this->get(route('panel.account.security'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('pendingTwoFactor.secret', session('panel.two_factor.pending_secret'))
            ->where('twoFactorEnabled', false)
            ->has('pendingTwoFactor.qrCode'));
});

test('beginning enrolment is a no-op when two factor is already enabled', function () {
    $this->staff->forceFill([
        'app_authentication_secret' => 'JBSWY3DPEHPK3PXP',
        'app_authentication_recovery_codes' => [Hash::make('existing-code')],
    ])->save();

    $this->post(route('panel.account.two-factor.store'));

    expect(session('panel.two_factor.pending_secret'))->toBeNull()
        ->and($this->staff->fresh()->app_authentication_secret)->toBe('JBSWY3DPEHPK3PXP');
});

test('a wrong code does not enable two factor', function () {
    $this->post(route('panel.account.two-factor.store'));

    $this->post(route('panel.account.two-factor.confirm'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    expect($this->staff->fresh()->app_authentication_secret)->toBeNull();
});

test('a valid code enables two factor and reveals recovery codes once', function () {
    $this->post(route('panel.account.two-factor.store'));
    $secret = session('panel.two_factor.pending_secret');

    $this->post(route('panel.account.two-factor.confirm'), [
        'code' => app(Google2FA::class)->getCurrentOtp($secret),
    ])->assertRedirect();

    $staff = $this->staff->fresh();
    expect($staff->app_authentication_secret)->toBe($secret)
        ->and($staff->app_authentication_recovery_codes)->toHaveCount(8)
        ->and(session()->has('panel.two_factor.pending_secret'))->toBeFalse();

    $this->get(route('panel.account.security'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('twoFactorEnabled', true)
            ->has('recoveryCodes', 8));

    // Flashed once — gone on the next request.
    $this->get(route('panel.account.security'))
        ->assertInertia(fn (Assert $page) => $page->where('recoveryCodes', null));
});

test('confirmation is rate limited after five failed attempts', function () {
    $this->post(route('panel.account.two-factor.store'));
    $secret = session('panel.two_factor.pending_secret');

    foreach (range(1, 5) as $attempt) {
        $this->post(route('panel.account.two-factor.confirm'), ['code' => '000000']);
    }

    $this->post(route('panel.account.two-factor.confirm'), [
        'code' => app(Google2FA::class)->getCurrentOtp($secret),
    ])->assertSessionHasErrors('code');

    expect($this->staff->fresh()->app_authentication_secret)->toBeNull();
});

test('regenerating recovery codes requires the current password', function () {
    $this->staff->forceFill([
        'app_authentication_secret' => 'JBSWY3DPEHPK3PXP',
        'app_authentication_recovery_codes' => [Hash::make('old-code')],
    ])->save();

    $this->post(route('panel.account.two-factor.recovery-codes'), [
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('password');

    expect($this->staff->fresh()->app_authentication_recovery_codes)->toHaveCount(1);
});

test('regenerating recovery codes replaces the old set', function () {
    $this->staff->forceFill([
        'app_authentication_secret' => 'JBSWY3DPEHPK3PXP',
        'app_authentication_recovery_codes' => [Hash::make('old-code')],
    ])->save();

    $this->post(route('panel.account.two-factor.recovery-codes'), [
        'password' => 'password',
    ])->assertRedirect();

    $codes = $this->staff->fresh()->app_authentication_recovery_codes;

    expect($codes)->toHaveCount(8)
        ->and(collect($codes)->contains(fn (string $hash) => Hash::check('old-code', $hash)))->toBeFalse();
});

test('disabling two factor requires the current password and clears both columns', function () {
    $this->staff->forceFill([
        'app_authentication_secret' => 'JBSWY3DPEHPK3PXP',
        'app_authentication_recovery_codes' => [Hash::make('a-code')],
    ])->save();

    $this->delete(route('panel.account.two-factor.destroy'), [
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('password');

    $this->delete(route('panel.account.two-factor.destroy'), [
        'password' => 'password',
    ])->assertRedirect();

    $staff = $this->staff->fresh();
    expect($staff->app_authentication_secret)->toBeNull()
        ->and($staff->app_authentication_recovery_codes)->toBeNull();
});
