<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Auth\AppAuthentication;
use Lunar\Tests\Panel\TestCase;
use PragmaRX\Google2FA\Google2FA;

uses(TestCase::class);

test('panel-enabled two factor matches filament v5 column semantics', function () {
    $staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($staff, 'staff');

    $this->post(route('panel.account.two-factor.store'));
    $secret = session('panel.two_factor.pending_secret');
    $this->post(route('panel.account.two-factor.confirm'), [
        'code' => app(Google2FA::class)->getCurrentOtp($secret),
    ]);
    $plaintextCodes = session('panel.two_factor.recovery_codes');

    $row = DB::table((new Staff)->getTable())->where('id', $staff->id)->first();

    // Secret: Crypt payload of a 16-char base32 string — what Filament's
    // `encrypted` cast reads.
    expect(Crypt::decryptString($row->app_authentication_secret))->toMatch('/^[A-Z2-7]{16}$/');

    // Recovery codes: Crypt payload of a JSON array of bcrypt hashes,
    // each verifying one of the plaintexts shown to the staff member —
    // what Filament's `encrypted:array` cast + Hash::check flow expects.
    $hashes = json_decode(Crypt::decryptString($row->app_authentication_recovery_codes), true);

    expect($hashes)->toHaveCount(8)
        ->each->toStartWith('$2y$');

    foreach ($plaintextCodes as $index => $code) {
        expect(Hash::check($code, $hashes[$index]))->toBeTrue();
    }
});

test('a staff record written the filament way authenticates through the panel', function () {
    // Write raw column values exactly as Filament's casts would, bypassing
    // the panel entirely.
    $staff = Staff::factory()->create();

    DB::table((new Staff)->getTable())->where('id', $staff->id)->update([
        'app_authentication_secret' => Crypt::encryptString('JBSWY3DPEHPK3PXP'),
        'app_authentication_recovery_codes' => Crypt::encryptString(json_encode([
            Hash::make('filament-code-1'),
        ])),
    ]);

    $this->post(route('panel.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
    ])->assertRedirect(route('panel.two-factor.challenge'));

    $this->post(route('panel.two-factor.challenge.store'), [
        'code' => app(Google2FA::class)->getCurrentOtp('JBSWY3DPEHPK3PXP'),
    ])->assertRedirect(route('panel.dashboard'));

    expect(Auth::guard('staff')->id())->toBe($staff->id);

    $this->post(route('panel.logout'));

    // Recovery codes written the Filament way also verify and consume.
    expect(app(AppAuthentication::class)->verifyAndConsumeRecoveryCode($staff->fresh(), 'filament-code-1'))->toBeTrue()
        ->and($staff->fresh()->app_authentication_recovery_codes)->toHaveCount(0);
});
