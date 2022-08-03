<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasMedia as TraitsHasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class Asset extends BaseModel implements HasMedia
{
    use TraitsHasMedia;

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    public function file()
    {
        return $this->morphOne(config('media-library.media_model'), 'model');
    }
}
