<?php

namespace Lunar\Api\Models;

use Carbon\CarbonInterface;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lunar\Api\Database\Factories\ApiKeyFactory;
use Lunar\Api\Support\NewApiKey;
use Lunar\Core\Models\Base;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Staff;

/**
 * A bearer credential for the admin surface. The plaintext is shown once at
 * issuance; only its SHA-256 is stored. A key is the principal of its own
 * requests, so an ownerless integration key is distinguishable in the
 * activity log from a human.
 *
 * @property int $id
 * @property string $public_id
 * @property string $name
 * @property string $token_prefix
 * @property string $token_hash
 * @property array<int, string> $abilities
 * @property ?int $staff_id
 * @property ?Carbon $last_used_at
 * @property ?Carbon $expires_at
 * @property ?Carbon $revoked_at
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class ApiKey extends Base implements AuthenticatableContract, AuthorizableContract
{
    use AuthenticatableTrait;
    use Authorizable;
    use HasFactory;
    use HasPublicId;

    public const TOKEN_LENGTH = 48;

    /** @var array<int, string> */
    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /** @var array<int, string> */
    protected $hidden = [
        'token_hash',
    ];

    protected static function newFactory(): ApiKeyFactory
    {
        return ApiKeyFactory::new();
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Issue a key. The returned plaintext token is the only time it is available.
     *
     * @param  array<int, string>  $abilities
     */
    public static function generate(string $name, array $abilities, ?Staff $staff = null, ?CarbonInterface $expiresAt = null): NewApiKey
    {
        $plainText = Str::random(self::TOKEN_LENGTH);

        $key = static::query()->create([
            'name' => $name,
            'token_prefix' => substr($plainText, 0, 8),
            'token_hash' => static::hashToken($plainText),
            'abilities' => array_values(array_unique($abilities)),
            'staff_id' => $staff?->getKey(),
            'expires_at' => $expiresAt,
        ]);

        return new NewApiKey($key, $plainText);
    }

    public static function hashToken(string $plainText): string
    {
        return hash('sha256', $plainText);
    }

    public static function findByToken(string $plainText): ?static
    {
        return static::query()->where('token_hash', static::hashToken($plainText))->first();
    }

    /** The key for a bearer token, if it exists and is neither revoked nor expired. */
    public static function findActiveByToken(string $plainText): ?static
    {
        return static::query()->active()->where('token_hash', static::hashToken($plainText))->first();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at')
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function hasAbility(string $ability): bool
    {
        $abilities = $this->abilities ?? [];

        return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
    }

    /**
     * The panel's and Filament's permission gates ask their principal this;
     * answering from the key's abilities lets the same policies gate a key.
     */
    public function hasPermissionTo(mixed $permission, ?string $guardName = null): bool
    {
        return $this->hasAbility(is_string($permission) ? $permission : (string) $permission->name);
    }

    /** Those gates also short-circuit on `$user->admin`; a wildcard key is the equivalent. */
    public function getAdminAttribute(): bool
    {
        return $this->hasAbility('*');
    }

    public function revoke(): void
    {
        $this->forceFill(['revoked_at' => now()])->save();
    }

    /** Record use at most once a minute so a busy integration is not a write per request. */
    public function markUsed(): void
    {
        if ($this->last_used_at && $this->last_used_at->gt(now()->subMinute())) {
            return;
        }

        $this->forceFill(['last_used_at' => now()])->saveQuietly();
    }
}
