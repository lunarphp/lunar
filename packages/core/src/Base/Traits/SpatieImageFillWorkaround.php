<?php

namespace Lunar\Base\Traits;

use Spatie\Image\Enums\Fit;

trait SpatieImageFillWorkaround
{
    protected $fill;

    public function __construct()
    {
        $this->fill = phpversion() >= 8.2
            ? class_exists('Spatie\Image\Enums\Fit') ? Fit::Fill : null
            : Manipulations::FIT_FILL;
    }
}

class Manipulations
{
    const FIT_FILL = 'fit_fill';
}
