<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: reconcile staff two-factor storage.
 *
 * Two gaps on the staff table, handled in order:
 *
 * 1. Column names. Lunar 1.5 renamed the two-factor columns to Filament's
 *    app-authentication names — two_factor_secret → app_authentication_secret,
 *    two_factor_recovery_codes → app_authentication_recovery_codes, and dropped
 *    the redundant two_factor_confirmed_at — via an admin-package migration. A
 *    store upgraded straight from a pre-1.5 line never ran it, so it lands with the
 *    old names and the v2 Staff model (which reads app_authentication_*) sees no
 *    2FA. Apply the rename here when the v1 columns are still present; a 1.5+ store
 *    already has the v2 names and is left untouched.
 *
 * 2. Encoding. v1 stored the secret and recovery codes as encrypt(serialize(...))
 *    with plaintext recovery codes. v2's panel (spec 0049, #2558) matches
 *    Filament's app-authentication: the secret behind an `encrypted` cast read with
 *    decryptString (no unserialize), recovery codes behind `encrypted:array`
 *    verified bcrypt-hashed. Carried across verbatim, the secret then decrypts to
 *    the serialization wrapper (s:16:"JBSW...";) and google2fa throws "Invalid
 *    characters in the base32 string", while the recovery value json_decodes to
 *    null. Unwrap the serialized secret and re-encrypt it without the wrapper, and
 *    bcrypt-hash each still-plaintext recovery code — matching the `encrypted` /
 *    `encrypted:array` casts so the Staff model reads them back natively.
 *
 * Idempotent: the rename is guarded on the v1 columns' presence, a secret already a
 * plain base32 string fails the serialize probe, a recovery value already a JSON
 * array is left alone, and an already-hashed code is not re-hashed. One-way, no
 * down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $staff = $this->prefix.'staff';

        if (! Schema::hasTable($staff)) {
            return;
        }

        $this->renameLegacyColumns($staff);

        if (! Schema::hasColumn($staff, 'app_authentication_secret')
            || ! Schema::hasColumn($staff, 'app_authentication_recovery_codes')) {
            return;
        }

        DB::table($staff)
            ->where(function ($query): void {
                $query->whereNotNull('app_authentication_secret')
                    ->orWhereNotNull('app_authentication_recovery_codes');
            })
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($staff): void {
                foreach ($rows as $row) {
                    try {
                        $update = [];

                        $secret = $this->reencodeSecret($row->app_authentication_secret);

                        if ($secret !== null) {
                            $update['app_authentication_secret'] = $secret;
                        }

                        $codes = $this->reencodeRecoveryCodes($row->app_authentication_recovery_codes);

                        if ($codes !== null) {
                            $update['app_authentication_recovery_codes'] = $codes;
                        }

                        if ($update !== []) {
                            DB::table($staff)->where('id', $row->id)->update($update);
                        }
                    } catch (DecryptException) {
                        // A 2FA record this database's APP_KEY cannot decrypt — a stale
                        // value from a prior key rotation, say — is left as-is rather
                        // than aborting the whole upgrade; that staff member re-enrols.
                        // A key wrong for the entire database surfaces earlier in the
                        // upgrade, not just here.
                    }
                }
            });
    }

    /**
     * Apply Lunar 1.5's two-factor column rename when a pre-1.5 store upgraded
     * without it. A 1.5+ store already has the v2 names and is left untouched.
     */
    private function renameLegacyColumns(string $staff): void
    {
        // Guard each column independently: Fortify's migration adds and renames the
        // pair together, but a store part-way through a manual fix could carry only
        // one, and renaming a missing column would throw.
        $renameSecret = Schema::hasColumn($staff, 'two_factor_secret');
        $renameRecoveryCodes = Schema::hasColumn($staff, 'two_factor_recovery_codes');
        $dropConfirmedAt = Schema::hasColumn($staff, 'two_factor_confirmed_at');

        if (! $renameSecret && ! $renameRecoveryCodes && ! $dropConfirmedAt) {
            return;
        }

        Schema::table($staff, function (Blueprint $table) use ($renameSecret, $renameRecoveryCodes, $dropConfirmedAt): void {
            if ($renameSecret) {
                $table->renameColumn('two_factor_secret', 'app_authentication_secret');
            }

            if ($renameRecoveryCodes) {
                $table->renameColumn('two_factor_recovery_codes', 'app_authentication_recovery_codes');
            }

            if ($dropConfirmedAt) {
                $table->dropColumn('two_factor_confirmed_at');
            }
        });
    }

    /**
     * Returns the re-encrypted secret when the stored value is v1's serialized
     * wrapper, or null when it is already the plain v2 form (or absent).
     */
    private function reencodeSecret(?string $encrypted): ?string
    {
        if ($encrypted === null || $encrypted === '') {
            return null;
        }

        $plain = @unserialize(Crypt::decryptString($encrypted), ['allowed_classes' => false]);

        return is_string($plain) ? Crypt::encryptString($plain) : null;
    }

    /**
     * Returns the re-encrypted, bcrypt-hashed recovery-code set when the stored
     * value is v1's serialized/plaintext form, or null when it is already the v2
     * `encrypted:array` form (or absent).
     */
    private function reencodeRecoveryCodes(?string $encrypted): ?string
    {
        if ($encrypted === null || $encrypted === '') {
            return null;
        }

        $decrypted = Crypt::decryptString($encrypted);

        // The v2 `encrypted:array` form decrypts straight to a JSON array; leave it.
        // This relies on v1 always carrying the serialize wrapper (Fortify stored
        // `encrypt(serialize(json_encode($codes)))`), so a v1 value never decrypts to
        // bare JSON — otherwise plaintext codes could be mistaken for a done set.
        if (is_array(json_decode($decrypted, true))) {
            return null;
        }

        $json = @unserialize($decrypted, ['allowed_classes' => false]);

        if (! is_string($json)) {
            return null;
        }

        $codes = json_decode($json, true);

        if (! is_array($codes)) {
            return null;
        }

        $hashed = array_values(array_map(
            fn ($code): string => Hash::isHashed((string) $code) ? (string) $code : Hash::make((string) $code),
            $codes,
        ));

        return Crypt::encryptString(json_encode($hashed));
    }
};
