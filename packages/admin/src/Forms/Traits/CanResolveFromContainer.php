<?php

namespace GetCandy\Hub\Forms\Traits;

trait CanResolveFromContainer
{
    public static function make(string $name): static
    {
        // @todo Add default label translation

        return resolve(static::class, ['name' => $name]);
    }
}
