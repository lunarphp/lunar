<?php

namespace Lunar\Admin\Support\Resources\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

trait HasLunarPermissions
{
    protected static ?string $permission = null;

    public static function registerNavigationItems(): void
    {
        if (! static::hasPermission()) {
            return;
        }

        parent::registerNavigationItems();
    }

    public static function can(string|\UnitEnum $action, ?Model $record = null): bool
    {
        return static::hasPermission();
    }

    protected static function hasPermission(): bool
    {
        if (! static::$permission) {
            return true;
        }

        $user = Filament::auth()->user();

        return $user->can(static::$permission);
    }
}
