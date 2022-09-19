<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasDefaultRecord;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Database\Factories\ChannelFactory;

class Channel extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;
    use HasDefaultRecord;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     *
     * @return \Lunar\Database\Factories\ChannelFactory
     */
    protected static function newFactory(): ChannelFactory
    {
        return ChannelFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Mutator for formatting the handle to a slug.
     *
     * @param  string  $val
     * @return void
     */
    public function setHandleAttribute($val)
    {
        $this->attributes['handle'] = Str::slug($val);
    }

    /**
     * Get the parent channelable model.
     */
    public function channelable()
    {
        return $this->morphTo();
    }
}
