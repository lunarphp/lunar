<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\PositionManifestInterface;

/**
 * Class PositionManifest.
 *
 * @method static void saving(\Illuminate\Database\Eloquent\Model $model)
 * @method static array constraints(\Illuminate\Database\Eloquent\Model $model)
 * @method static void query(\Illuminate\Database\Eloquent\Builder $query, int $position, array $constraints = [])
 * @method static void queryPosition(\Illuminate\Database\Eloquent\Builder $query, int $position)
 * @method static void queryUniqueConstraints(\Illuminate\Database\Eloquent\Builder $query, array $constraints)
 * @method static void registerBlueprintMacros()
 * 
 * @see \Lunar\Base\PositionManifest
 */
class PositionManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return PositionManifestInterface::class;
    }
}