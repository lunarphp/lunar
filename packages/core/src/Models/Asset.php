<?php

namespace Lunar\Models;

use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMedia as TraitsHasMedia;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property int $id
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
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
