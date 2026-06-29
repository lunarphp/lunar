<?php

namespace Lunar\Core\Models\Concerns;

trait HasExtendableCasts
{
    /**
     * Attribute casts registered at runtime, keyed by model class.
     *
     * @var array<class-string, array<string, string>>
     */
    protected static array $extendedCasts = [];

    /**
     * Register additional attribute casts for this model from outside the class
     * (e.g. a service provider), for columns a consumer has added. Accepts the
     * same values as the `casts()` method, including custom cast classes.
     *
     * @param  array<string, string>  $casts
     */
    public static function addCasts(array $casts): void
    {
        static::$extendedCasts[static::class] = array_merge(
            static::$extendedCasts[static::class] ?? [],
            $casts,
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, string>
     */
    public function getCasts()
    {
        return array_merge(
            parent::getCasts(),
            static::$extendedCasts[static::class] ?? [],
        );
    }
}
