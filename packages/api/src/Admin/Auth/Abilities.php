<?php

namespace Lunar\Api\Admin\Auth;

use Illuminate\Support\Facades\Gate;
use Lunar\Api\Models\ApiKey;
use Lunar\Core\Auth\Manifest;

/**
 * The admin surface's ability vocabulary is the staff permission manifest,
 * so a policy or a `requires()` on a field works identically for a staff
 * session and a key.
 */
final class Abilities
{
    public const ALL = '*';

    public const CATALOG_READ = 'catalog:read';

    public const SALES_READ = 'sales:read';

    public const SETTINGS_READ = 'settings:read';

    public const MANAGE_API_KEYS = 'settings:manage-api-keys';

    /**
     * Every ability a key may be granted: the manifest's permissions plus the wildcard.
     *
     * @return array<int, string>
     */
    public static function all(Manifest $manifest): array
    {
        $handles = $manifest->getPermissions()->pluck('handle')->all();

        return array_values(array_unique([self::ALL, ...$manifest->getBasePermissions(), ...$handles]));
    }

    /** Resolve abilities from the key the way the panel's gate resolves them from roles. */
    public static function registerGate(): void
    {
        Gate::after(function ($user, string $ability): ?bool {
            return $user instanceof ApiKey ? $user->hasAbility($ability) : null;
        });
    }
}
