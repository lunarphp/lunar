<?php

namespace Lunar\Admin\Support\Resources\Concerns;

use ReflectionClass;

trait ResolvesModelContract
{
    public static function getModel(): string
    {
        $class = new ReflectionClass(static::$model);

        if ($class->isInterface()) {
            return app()->get(static::$model)::class;
        }

        return parent::getModel();
    }
}
