<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasDefaultRecord;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Database\Factories\ChannelFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property bool $default
 * @property ?string $url
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 * @property ?\Illuminate\Support\Carbon $deleted_at
 */
class Channel extends BaseModel
{
    use HasDefaultRecord;
    use HasFactory;
    use HasMacros;
    use LogsActivity;
    use SoftDeletes;

    /**
     * Return a new factory instance for the model.
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

    /**
     * Return the products relationship
     */
    public function products(): MorphToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->morphedByMany(
            Product::class,
            'channelable',
            "{$prefix}channelables"
        );
    }
}
