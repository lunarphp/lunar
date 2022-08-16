<?php

namespace GetCandy\Hub\Forms\Managers;

use GetCandy\Hub\Forms\Traits\CanResolveFromContainer;
use Illuminate\Support\Manager;

class ImageManager extends Manager
{
    use CanResolveFromContainer;

    public function getDefaultDriver(): string
    {
        return config('getcandy.images.driver', 's3');
    }
}
