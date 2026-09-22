<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Core\TestCase;
use Spatie\Permission\Guard;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('app authentication columns are encrypted at rest and hidden', function () {
    $staff = Staff::factory()->create([
        'app_authentication_secret' => 'JBSWY3DPEHPK3PXP',
        'app_authentication_recovery_codes' => ['hash-one', 'hash-two'],
    ]);

    expect(Crypt::decryptString($staff->getRawOriginal('app_authentication_secret')))
        ->toBe('JBSWY3DPEHPK3PXP')
        ->and(json_decode(Crypt::decryptString($staff->getRawOriginal('app_authentication_recovery_codes')), true))
        ->toBe(['hash-one', 'hash-two'])
        ->and($staff->fresh()->app_authentication_secret)->toBe('JBSWY3DPEHPK3PXP')
        ->and($staff->fresh()->app_authentication_recovery_codes)->toBe(['hash-one', 'hash-two'])
        ->and($staff->toArray())->not->toHaveKey('app_authentication_secret')
        ->and($staff->toArray())->not->toHaveKey('app_authentication_recovery_codes');
});

test('the withTwoFactor factory state enables two factor', function () {
    $staff = Staff::factory()->withTwoFactor()->create();

    expect($staff->app_authentication_secret)->toBe('JBSWY3DPEHPK3PXP')
        ->and($staff->app_authentication_recovery_codes)->toHaveCount(8);
});

test('the Spatie guard follows the configured staff guard', function () {
    expect(Guard::getNames(new Staff)->all())->toBe(['staff']);

    config(['lunar.staff.guard' => 'backoffice']);

    expect((new Staff)->guardName())->toBe('backoffice')
        ->and(Guard::getNames(new Staff)->all())->toBe(['backoffice']);
});
