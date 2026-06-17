<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\HoldReasonManifest;

/**
 * @method static array<string, string> all()
 * @method static string|null label(?string $key)
 * @method static HoldReasonManifest set(array<string, string> $reasons)
 * @method static HoldReasonManifest add(string $key, string $label)
 * @method static HoldReasonManifest forget(string ...$keys)
 *
 * @see HoldReasonManifest
 */
class HoldReasons extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return HoldReasonManifest::class;
    }
}
