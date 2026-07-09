<?php

use Illuminate\Support\Facades\Hash;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Auth\AppAuthentication;
use Lunar\Tests\Panel\TestCase;
use PragmaRX\Google2FA\Google2FA;

uses(TestCase::class);

beforeEach(function () {
    $this->auth = app(AppAuthentication::class);
});

test('generates a 16 character base32 secret', function () {
    expect($this->auth->generateSecret())->toMatch('/^[A-Z2-7]{16}$/');
});

test('verifies a current totp code and rejects a wrong one', function () {
    $secret = $this->auth->generateSecret();
    $code = app(Google2FA::class)->getCurrentOtp($secret);

    expect($this->auth->verifyCode($secret, $code))->toBeTrue()
        ->and($this->auth->verifyCode($secret, '000000'))->toBeFalse();
});

test('prevents reuse of a code when asked', function () {
    $secret = $this->auth->generateSecret();
    $code = app(Google2FA::class)->getCurrentOtp($secret);

    expect($this->auth->verifyCode($secret, $code, preventReuse: true))->toBeTrue()
        ->and($this->auth->verifyCode($secret, $code, preventReuse: true))->toBeFalse();
});

test('generates eight recovery codes in the filament format', function () {
    $codes = $this->auth->generateRecoveryCodes();

    expect($codes)->toHaveCount(8)
        ->each->toMatch('/^[a-zA-Z0-9]{10}-[a-zA-Z0-9]{10}$/');
});

test('hashes recovery codes with bcrypt', function () {
    $hashed = $this->auth->hashRecoveryCodes(['alpha-code', 'beta-code']);

    expect($hashed)->toHaveCount(2)
        ->and(Hash::check('alpha-code', $hashed[0]))->toBeTrue();
});

test('consumes a matching recovery code exactly once', function () {
    $staff = Staff::factory()->create([
        'app_authentication_secret' => 'JBSWY3DPEHPK3PXP',
        'app_authentication_recovery_codes' => $this->auth->hashRecoveryCodes(['known-code-1', 'known-code-2']),
    ]);

    expect($this->auth->verifyAndConsumeRecoveryCode($staff, 'known-code-1'))->toBeTrue()
        ->and($staff->fresh()->app_authentication_recovery_codes)->toHaveCount(1)
        ->and($this->auth->verifyAndConsumeRecoveryCode($staff, 'known-code-1'))->toBeFalse()
        ->and($this->auth->verifyAndConsumeRecoveryCode($staff, 'known-code-2'))->toBeTrue();
});

test('renders a qr code as an svg data uri', function () {
    $uri = $this->auth->qrCodeDataUri('Lunar', 'staff@example.com', 'JBSWY3DPEHPK3PXP');

    expect($uri)->toStartWith('data:image/svg+xml;base64,')
        ->and(base64_decode(substr($uri, 26)))->toContain('<svg');
});
