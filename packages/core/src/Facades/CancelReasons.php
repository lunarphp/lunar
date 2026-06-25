<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\CancelReasonManifest;

/**
 * @method static array<string, string> all()
 * @method static string|null label(?string $key)
 * @method static CancelReasonManifest set(array<string, string> $reasons)
 * @method static CancelReasonManifest add(string $key, string $label)
 * @method static CancelReasonManifest forget(string ...$keys)
 *
 * @see CancelReasonManifest
 */
class CancelReasons extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return CancelReasonManifest::class;
    }
}
