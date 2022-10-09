<?php

namespace Lunar\Hub\Forms\Managers;

use Illuminate\Support\Manager;
use Lunar\Hub\Forms\Traits\CanResolveFromContainer;

class ImageManager extends Manager
{
    use CanResolveFromContainer;

    public function getDefaultDriver(): string
    {
        return config('lunar.images.driver', 's3');
    }
}
