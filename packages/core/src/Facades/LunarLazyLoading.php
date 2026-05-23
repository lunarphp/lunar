<?php

namespace Lunar\Core\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool enabled()
 * @method static void handleViolationUsing(?callable $callback)
 * @method static void handleMissingAttributeUsing(?callable $callback)
 * @method static void handleDiscardedAttributeUsing(?callable $callback)
 * @method static void handleViolation(Model $model, string $relation)
 * @method static void handleMissingAttribute(Model $model, string $key)
 * @method static void handleDiscardedAttributes(Model $model, array $keys)
 *
 * @see \Lunar\Core\Base\LunarLazyLoading
 */
class LunarLazyLoading extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return \Lunar\Core\Base\LunarLazyLoading::class;
    }
}
