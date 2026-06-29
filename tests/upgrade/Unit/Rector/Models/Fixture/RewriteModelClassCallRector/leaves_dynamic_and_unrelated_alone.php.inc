<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Models\Fixture\RewriteModelClassCallRector;

class Registry
{
    public static function modelClass(): string
    {
        return self::class;
    }
}

function untouched(string $model): array
{
    return [
        $model::modelClass(),
        Registry::modelClass('extra-arg'),
    ];
}
