<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasDefaultRecord;
use GetCandy\Base\Traits\LogsActivity;
use GetCandy\Database\Factories\ChannelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Channel extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;
    use HasDefaultRecord;

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\ChannelFactory
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
