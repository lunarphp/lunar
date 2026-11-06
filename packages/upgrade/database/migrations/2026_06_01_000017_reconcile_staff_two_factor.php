<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
 *    2FA. Apply the rename here when a v1 column is still present and its v2 target
 *    is absent; a 1.5+ store (or one part-way through a manual fix that already has
 *    the v2 column) is left untouched, the data pass reconciling the v2 column
 *    directly. A half-finished enrolment
 *    (secret written but two_factor_confirmed_at still null) was 2FA-off in v1, so
 *    its columns are cleared before the rename rather than promoted to an active v2
 *    secret the user never confirmed.
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
 * Idempotent: the rename is guarded on the v1 source being present and the v2
 * target absent, a secret already a plain base32 string fails the serialize probe,
 * a recovery value already a JSON array is left alone, and an already-hashed code is
 * not re-hashed. One-way, no down().
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

        // Handle the two columns independently: a store part-way through a manual fix
        // may carry only one of them, and selecting or updating a missing column would throw.
        $hasSecret = Schema::hasColumn($staff, 'app_authentication_secret');
        $hasRecoveryCodes = Schema::hasColumn($staff, 'app_authentication_recovery_codes');

        if (! $hasSecret && ! $hasRecoveryCodes) {
            return;
        }

        DB::table($staff)
            ->where(function ($query) use ($hasSecret, $hasRecoveryCodes): void {
                if ($hasSecret) {
                    $query->orWhereNotNull('app_authentication_secret');
                }

                if ($hasRecoveryCodes) {
                    $query->orWhereNotNull('app_authentication_recovery_codes');
                }
            })
            ->chunkById(500, function ($rows) use ($staff, $hasSecret, $hasRecoveryCodes): void {
                foreach ($rows as $row) {
                    try {
                        $update = [];

                        if ($hasSecret) {
                            $secret = $this->reencodeSecret($row->app_authentication_secret);

                            if ($secret !== null) {
                                $update['app_authentication_secret'] = $secret;
                            }
                        }

                        if ($hasRecoveryCodes) {
                            $codes = $this->reencodeRecoveryCodes($row->app_authentication_recovery_codes);

                            if ($codes !== null) {
                                $update['app_authentication_recovery_codes'] = $codes;
                            }
                        }

                        if ($update !== []) {
                            DB::table($staff)->where('id', $row->id)->update($update);
                        }
                    } catch (DecryptException) {
                        // A 2FA record this database's APP_KEY cannot decrypt — a stale
                        // value from a prior key rotation, say — is left untouched rather
                        // than aborting the whole upgrade. Clearing it would be a silent
                        // 2FA downgrade, and it was equally unusable in v1. The staff
                        // member cannot self-serve a re-enrolment (the encrypted cast
                        // throws when their challenge reads the stale secret), so an admin
                        // must clear the columns — warn so the operator knows who needs
                        // attention. A key wrong for the entire database surfaces earlier
                        // in the upgrade, not just here.
                        Log::warning(
                            'Skipped staff two-factor reconciliation: the stored value could not be decrypted with the current APP_KEY.',
                            ['staff_id' => $row->id],
                        );
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
        // Guard each rename on the v1 (source) column being present AND the v2
        // (target) column being absent. Fortify's migration renames the pair
        // together, but a store part-way through a manual fix could carry only one —
        // or could have COPIED (not renamed) two_factor_secret into
        // app_authentication_secret, leaving both columns present, at which point
        // renameColumn() would throw and abort the whole upgrade. When both exist,
        // skipping the rename is enough: the data pass below reconciles whatever is
        // already in the v2 column.
        $renameSecret = Schema::hasColumn($staff, 'two_factor_secret')
            && ! Schema::hasColumn($staff, 'app_authentication_secret');
        $renameRecoveryCodes = Schema::hasColumn($staff, 'two_factor_recovery_codes')
            && ! Schema::hasColumn($staff, 'app_authentication_recovery_codes');
        $hasConfirmedAt = Schema::hasColumn($staff, 'two_factor_confirmed_at');

        if (! $renameSecret && ! $renameRecoveryCodes && ! $hasConfirmedAt) {
            return;
        }

        // Discard half-finished v1 enrolments before they become active v2 2FA. The
        // Fortify-derived plugin writes the secret and recovery codes when enrolment
        // STARTS and only stamps two_factor_confirmed_at once the user verifies a TOTP
        // code — until then v1 treats 2FA as off. v2's AppAuthentication::isEnabled()
        // is just filled($secret), and the challenge offers no email fallback once a
        // secret exists, so carrying an unconfirmed secret across would lock that staff
        // member out with a TOTP they never finished setting up.
        if ($hasConfirmedAt) {
            $clear = [];

            if ($renameSecret) {
                $clear['two_factor_secret'] = null;
            }

            if ($renameRecoveryCodes) {
                $clear['two_factor_recovery_codes'] = null;
            }

            if ($clear !== []) {
                DB::table($staff)->whereNull('two_factor_confirmed_at')->update($clear);
            }
        }

        Schema::table($staff, function (Blueprint $table) use ($renameSecret, $renameRecoveryCodes, $hasConfirmedAt): void {
            if ($renameSecret) {
                $table->renameColumn('two_factor_secret', 'app_authentication_secret');
            }

            if ($renameRecoveryCodes) {
                $table->renameColumn('two_factor_recovery_codes', 'app_authentication_recovery_codes');
            }

            if ($hasConfirmedAt) {
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
