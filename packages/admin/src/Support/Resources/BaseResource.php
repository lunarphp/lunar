<?php

namespace Lunar\Admin\Support\Resources;

use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Concerns;

class BaseResource extends Resource
{
    use Concerns\CallsHooks;
    use Concerns\ExtendsForms;
    use Concerns\ExtendsRelationManagers;
    use Concerns\ExtendsTables;

    protected static ?string $permission = null;

    public static function registerNavigationItems(): void
    {
        if (! static::hasPermission()) {
            return;
        }

        parent::registerNavigationItems();
    }

    public static function can(string $action, Model $record = null): bool
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

    public static function getModel(): string
    {
        $class = new \ReflectionClass(static::$model);

        if ($class->isInterface()) {
            return app()->get(static::$model)::class;
        }

        return static::$model ?? (string) str(class_basename(static::class))
            ->beforeLast('Resource')
            ->prepend('App\\Models\\');
    }
}
