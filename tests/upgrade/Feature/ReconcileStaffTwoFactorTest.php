<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class);

/**
 * Isolated prefix so this stands up its own throwaway schema without touching
 * the shared lunar_* tables.
 */
const STAFF_2FA_UPG_PREFIX = 'upg2fa_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => STAFF_2FA_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(STAFF_2FA_UPG_PREFIX.'staff');
});

function staffTwoFactorMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*reconcile_staff_two_factor.php');

    return require $path[0];
}

/**
 * Stand up a staff table. `renamed` picks the shape: a pre-1.5 store still has the
 * two_factor_* columns (plus the redundant confirmed_at); a 1.5+ store already has
 * the app_authentication_* names.
 */
function createStaffTable(bool $renamed): void
{
    Schema::create(STAFF_2FA_UPG_PREFIX.'staff', function (Blueprint $table) use ($renamed) {
        $table->id();
        $table->string('email')->unique();
        $table->string('password');

        if ($renamed) {
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
        } else {
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
        }

        $table->timestamps();
    });
}

/**
 * Insert a staff row whose 2FA columns carry v1's encoding: Crypt::encrypt()
 * serializes by default, and recovery codes are stored plaintext inside the JSON.
 *
 * @param  array<int, string>  $plaintextCodes
 * @param  array<string, mixed>  $extra  further columns (e.g. two_factor_confirmed_at)
 */
function insertLegacyStaff(int $id, string $secretColumn, string $recoveryColumn, string $secret, array $plaintextCodes, array $extra = []): void
{
    DB::table(STAFF_2FA_UPG_PREFIX.'staff')->insert(array_merge([
        'id' => $id,
        'email' => "staff{$id}@example.com",
        'password' => bcrypt('secret'),
        $secretColumn => Crypt::encrypt($secret),
        $recoveryColumn => Crypt::encrypt(json_encode($plaintextCodes)),
        'created_at' => now(),
        'updated_at' => now(),
    ], $extra));
}

/**
 * Insert a staff row already in the v2 shape (renamed columns): the secret behind
 * encryptString (no serialize) and an encrypted:array of bcrypt-hashed codes.
 *
 * @param  array<int, string>  $plaintextCodes
 */
function insertV2Staff(int $id, string $secret, array $plaintextCodes): void
{
    DB::table(STAFF_2FA_UPG_PREFIX.'staff')->insert([
        'id' => $id,
        'email' => "staff{$id}@example.com",
        'password' => bcrypt('secret'),
        'app_authentication_secret' => Crypt::encryptString($secret),
        'app_authentication_recovery_codes' => Crypt::encryptString(json_encode(
            array_map(fn (string $code): string => Hash::make($code), $plaintextCodes),
        )),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('it renames pre-1.5 columns and re-encodes the secret and recovery codes', function () {
    createStaffTable(renamed: false);
    insertLegacyStaff(1, 'two_factor_secret', 'two_factor_recovery_codes', 'JBSWY3DPEHPK3PXP', ['aaaa111111-bbbb222222', 'cccc333333-dddd444444'], ['two_factor_confirmed_at' => now()]);

    staffTwoFactorMigration()->up();

    $table = STAFF_2FA_UPG_PREFIX.'staff';

    // Columns renamed to the v2 shape; the redundant confirmation timestamp dropped.
    expect(Schema::hasColumn($table, 'app_authentication_secret'))->toBeTrue()
        ->and(Schema::hasColumn($table, 'app_authentication_recovery_codes'))->toBeTrue()
        ->and(Schema::hasColumn($table, 'two_factor_secret'))->toBeFalse()
        ->and(Schema::hasColumn($table, 'two_factor_confirmed_at'))->toBeFalse();

    $row = DB::table($table)->find(1);

    // Secret re-encrypted without the serialize wrapper: the `encrypted` cast (decryptString) now reads clean base32.
    expect(Crypt::decryptString($row->app_authentication_secret))->toBe('JBSWY3DPEHPK3PXP');

    // Recovery codes now a JSON array of bcrypt hashes the `encrypted:array` cast reads natively.
    $codes = json_decode(Crypt::decryptString($row->app_authentication_recovery_codes), true);
    expect($codes)->toBeArray()->toHaveCount(2)
        ->and(Hash::check('aaaa111111-bbbb222222', $codes[0]))->toBeTrue()
        ->and(Hash::check('cccc333333-dddd444444', $codes[1]))->toBeTrue();
});

test('it re-encodes an already-renamed (1.5) store', function () {
    createStaffTable(renamed: true);
    insertLegacyStaff(1, 'app_authentication_secret', 'app_authentication_recovery_codes', 'JBSWY3DPEHPK3PXP', ['aaaa111111-bbbb222222']);

    staffTwoFactorMigration()->up();

    $row = DB::table(STAFF_2FA_UPG_PREFIX.'staff')->find(1);

    expect(Crypt::decryptString($row->app_authentication_secret))->toBe('JBSWY3DPEHPK3PXP');

    $codes = json_decode(Crypt::decryptString($row->app_authentication_recovery_codes), true);
    expect($codes)->toBeArray()->toHaveCount(1)
        ->and(Hash::check('aaaa111111-bbbb222222', $codes[0]))->toBeTrue();
});

test('it is idempotent on an already-reconciled row', function () {
    createStaffTable(renamed: true);
    insertLegacyStaff(1, 'app_authentication_secret', 'app_authentication_recovery_codes', 'JBSWY3DPEHPK3PXP', ['aaaa111111-bbbb222222']);

    staffTwoFactorMigration()->up();
    $afterFirst = DB::table(STAFF_2FA_UPG_PREFIX.'staff')->find(1);

    // A reconciled secret fails the serialize probe and a hashed recovery set is
    // already a JSON array, so a second run skips both — the stored ciphertext is
    // left byte-identical, not re-encrypted.
    staffTwoFactorMigration()->up();
    $afterSecond = DB::table(STAFF_2FA_UPG_PREFIX.'staff')->find(1);

    expect($afterSecond->app_authentication_secret)->toBe($afterFirst->app_authentication_secret)
        ->and($afterSecond->app_authentication_recovery_codes)->toBe($afterFirst->app_authentication_recovery_codes);
});

test('it renames the columns but leaves a staff row without 2FA untouched', function () {
    createStaffTable(renamed: false);
    DB::table(STAFF_2FA_UPG_PREFIX.'staff')->insert([
        'id' => 1,
        'email' => 'nobody@example.com',
        'password' => bcrypt('secret'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    staffTwoFactorMigration()->up();

    $row = DB::table(STAFF_2FA_UPG_PREFIX.'staff')->find(1);

    expect(Schema::hasColumn(STAFF_2FA_UPG_PREFIX.'staff', 'app_authentication_secret'))->toBeTrue()
        ->and($row->app_authentication_secret)->toBeNull()
        ->and($row->app_authentication_recovery_codes)->toBeNull();
});

test('it reconciles a mixed set in one pass, leaving already-v2 and empty rows untouched', function () {
    createStaffTable(renamed: true);

    // v1-encoded, both columns -> re-encoded.
    insertLegacyStaff(1, 'app_authentication_secret', 'app_authentication_recovery_codes', 'JBSWY3DPEHPK3PXP', ['aaaa111111-bbbb222222']);
    // Already v2 -> skipped, left byte-identical.
    insertV2Staff(2, 'MFRGGZDFMZTWQ2LK', ['cccc333333-dddd444444']);
    // No 2FA -> untouched.
    DB::table(STAFF_2FA_UPG_PREFIX.'staff')->insert([
        'id' => 3, 'email' => 'staff3@example.com', 'password' => bcrypt('secret'), 'created_at' => now(), 'updated_at' => now(),
    ]);
    // v1 secret only, recovery null -> secret re-encoded, recovery stays null.
    DB::table(STAFF_2FA_UPG_PREFIX.'staff')->insert([
        'id' => 4, 'email' => 'staff4@example.com', 'password' => bcrypt('secret'),
        'app_authentication_secret' => Crypt::encrypt('NBSWY3DPEB3W64TMMQ'),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $v2Before = DB::table(STAFF_2FA_UPG_PREFIX.'staff')->find(2);

    staffTwoFactorMigration()->up();

    $table = STAFF_2FA_UPG_PREFIX.'staff';

    expect(Crypt::decryptString(DB::table($table)->find(1)->app_authentication_secret))->toBe('JBSWY3DPEHPK3PXP');

    $v2After = DB::table($table)->find(2);
    expect($v2After->app_authentication_secret)->toBe($v2Before->app_authentication_secret)
        ->and($v2After->app_authentication_recovery_codes)->toBe($v2Before->app_authentication_recovery_codes);

    expect(DB::table($table)->find(3)->app_authentication_secret)->toBeNull();

    $four = DB::table($table)->find(4);
    expect(Crypt::decryptString($four->app_authentication_secret))->toBe('NBSWY3DPEB3W64TMMQ')
        ->and($four->app_authentication_recovery_codes)->toBeNull();
});

test('it renames a pre-1.5 store that already lacks two_factor_confirmed_at', function () {
    Schema::create(STAFF_2FA_UPG_PREFIX.'staff', function (Blueprint $table) {
        $table->id();
        $table->string('email')->unique();
        $table->string('password');
        $table->text('two_factor_secret')->nullable();
        $table->text('two_factor_recovery_codes')->nullable();
        $table->timestamps();
    });
    insertLegacyStaff(1, 'two_factor_secret', 'two_factor_recovery_codes', 'JBSWY3DPEHPK3PXP', ['aaaa111111-bbbb222222']);

    staffTwoFactorMigration()->up();

    $table = STAFF_2FA_UPG_PREFIX.'staff';
    expect(Schema::hasColumn($table, 'app_authentication_secret'))->toBeTrue()
        ->and(Schema::hasColumn($table, 'two_factor_secret'))->toBeFalse()
        ->and(Crypt::decryptString(DB::table($table)->find(1)->app_authentication_secret))->toBe('JBSWY3DPEHPK3PXP');
});

test('it discards an unconfirmed pre-1.5 enrolment instead of promoting it to active 2FA', function () {
    createStaffTable(renamed: false);
    // Confirmed enrolment (two_factor_confirmed_at set) -> carried across and re-encoded.
    insertLegacyStaff(1, 'two_factor_secret', 'two_factor_recovery_codes', 'JBSWY3DPEHPK3PXP', ['aaaa111111-bbbb222222'], ['two_factor_confirmed_at' => now()]);
    // Enrolment started but never confirmed (confirmed_at null) -> 2FA was OFF in v1.
    insertLegacyStaff(2, 'two_factor_secret', 'two_factor_recovery_codes', 'MFRGGZDFMZTWQ2LK', ['cccc333333-dddd444444'], ['two_factor_confirmed_at' => null]);

    staffTwoFactorMigration()->up();

    $table = STAFF_2FA_UPG_PREFIX.'staff';

    // The confirmed member keeps working 2FA.
    expect(Crypt::decryptString(DB::table($table)->find(1)->app_authentication_secret))->toBe('JBSWY3DPEHPK3PXP');

    // The unconfirmed member comes out with NO 2FA (they re-enrol in v2) — not a filled
    // secret they never confirmed, which v2 would treat as active with no email fallback,
    // locking them out.
    $unconfirmed = DB::table($table)->find(2);
    expect($unconfirmed->app_authentication_secret)->toBeNull()
        ->and($unconfirmed->app_authentication_recovery_codes)->toBeNull();
});

test('it re-encodes a store that carries only the secret column', function () {
    // A part-way-fixed store with only the secret column present (no recovery column):
    // the data pass must handle it independently rather than bail because its pair is absent.
    Schema::create(STAFF_2FA_UPG_PREFIX.'staff', function (Blueprint $table) {
        $table->id();
        $table->string('email')->unique();
        $table->string('password');
        $table->text('app_authentication_secret')->nullable();
        $table->timestamps();
    });
    DB::table(STAFF_2FA_UPG_PREFIX.'staff')->insert([
        'id' => 1, 'email' => 'staff1@example.com', 'password' => bcrypt('secret'),
        'app_authentication_secret' => Crypt::encrypt('JBSWY3DPEHPK3PXP'),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    staffTwoFactorMigration()->up();

    expect(Crypt::decryptString(DB::table(STAFF_2FA_UPG_PREFIX.'staff')->find(1)->app_authentication_secret))->toBe('JBSWY3DPEHPK3PXP');
});

test('it skips the rename when a store carries both the v1 and v2 columns, reconciling the v2 column', function () {
    // A store part-way through a manual fix that COPIED (not renamed) two_factor_secret
    // into app_authentication_secret carries both columns. renameColumn() would throw on
    // the collision and abort the upgrade, so the rename is skipped and the data pass
    // reconciles whatever sits in the v2 column.
    Schema::create(STAFF_2FA_UPG_PREFIX.'staff', function (Blueprint $table) {
        $table->id();
        $table->string('email')->unique();
        $table->string('password');
        $table->text('two_factor_secret')->nullable();
        $table->text('two_factor_recovery_codes')->nullable();
        $table->text('app_authentication_secret')->nullable();
        $table->text('app_authentication_recovery_codes')->nullable();
        $table->timestamps();
    });

    // The v1 encoding was copied into both columns, so the v2 column still carries the
    // serialize wrapper that needs re-encoding.
    $legacySecret = Crypt::encrypt('JBSWY3DPEHPK3PXP');
    $legacyCodes = Crypt::encrypt(json_encode(['aaaa111111-bbbb222222']));
    DB::table(STAFF_2FA_UPG_PREFIX.'staff')->insert([
        'id' => 1, 'email' => 'staff1@example.com', 'password' => bcrypt('secret'),
        'two_factor_secret' => $legacySecret,
        'two_factor_recovery_codes' => $legacyCodes,
        'app_authentication_secret' => $legacySecret,
        'app_authentication_recovery_codes' => $legacyCodes,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    // Must not throw on the rename collision.
    staffTwoFactorMigration()->up();

    $table = STAFF_2FA_UPG_PREFIX.'staff';

    // The rename was skipped: the v1 columns are left in place (renaming onto the
    // existing v2 columns is what would have aborted the upgrade).
    expect(Schema::hasColumn($table, 'app_authentication_secret'))->toBeTrue()
        ->and(Schema::hasColumn($table, 'two_factor_secret'))->toBeTrue();

    // The data pass reconciled the v2 column: its serialize wrapper is now plain base32.
    $row = DB::table($table)->find(1);
    expect(Crypt::decryptString($row->app_authentication_secret))->toBe('JBSWY3DPEHPK3PXP');

    $codes = json_decode(Crypt::decryptString($row->app_authentication_recovery_codes), true);
    expect($codes)->toBeArray()->toHaveCount(1)
        ->and(Hash::check('aaaa111111-bbbb222222', $codes[0]))->toBeTrue();
});

test('it leaves an already-reconciled v2 column untouched when a store carries both columns', function () {
    // Both columns present, but the operator has ALREADY reconciled the v2 column by hand
    // to the v2 encoding (secret behind encryptString, recovery an array of bcrypt hashes).
    // The rename is skipped (both exist) and the data pass must be a no-op — the idempotency
    // guards (a plain secret fails the serialize probe, a hashed recovery set is already a
    // JSON array) leave the ciphertext byte-identical rather than re-encrypting it.
    Schema::create(STAFF_2FA_UPG_PREFIX.'staff', function (Blueprint $table) {
        $table->id();
        $table->string('email')->unique();
        $table->string('password');
        $table->text('two_factor_secret')->nullable();
        $table->text('two_factor_recovery_codes')->nullable();
        $table->text('app_authentication_secret')->nullable();
        $table->text('app_authentication_recovery_codes')->nullable();
        $table->timestamps();
    });

    // v1 encoding still in the old columns; the v2 column is already in v2 form.
    DB::table(STAFF_2FA_UPG_PREFIX.'staff')->insert([
        'id' => 1, 'email' => 'staff1@example.com', 'password' => bcrypt('secret'),
        'two_factor_secret' => Crypt::encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_recovery_codes' => Crypt::encrypt(json_encode(['aaaa111111-bbbb222222'])),
        'app_authentication_secret' => Crypt::encryptString('JBSWY3DPEHPK3PXP'),
        'app_authentication_recovery_codes' => Crypt::encryptString(json_encode([Hash::make('aaaa111111-bbbb222222')])),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $before = DB::table(STAFF_2FA_UPG_PREFIX.'staff')->find(1);

    staffTwoFactorMigration()->up();

    $after = DB::table(STAFF_2FA_UPG_PREFIX.'staff')->find(1);

    // The already-reconciled v2 columns are byte-identical — not re-encrypted.
    expect($after->app_authentication_secret)->toBe($before->app_authentication_secret)
        ->and($after->app_authentication_recovery_codes)->toBe($before->app_authentication_recovery_codes);

    // …and still read back correctly in the v2 form.
    expect(Crypt::decryptString($after->app_authentication_secret))->toBe('JBSWY3DPEHPK3PXP');
    $codes = json_decode(Crypt::decryptString($after->app_authentication_recovery_codes), true);
    expect(Hash::check('aaaa111111-bbbb222222', $codes[0]))->toBeTrue();
});
