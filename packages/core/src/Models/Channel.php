<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lunar\Core\Database\Factories\ChannelFactory;
use Lunar\Core\Models\Concerns\HasDefaultRecord;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\States\Channel\ChannelState;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property bool $default
 * @property ?string $url
 * @property ChannelState $status
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Channel extends Base implements Contracts\Channel
{
    use HasDefaultRecord;
    use HasFactory;
    use HasMacros;
    use HasStates;
    use LogsActivity;

    public $casts = [
        'status' => ChannelState::class,
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
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
     */
    public function setHandleAttribute(?string $val): void
    {
        $this->attributes['handle'] = Str::slug($val);
    }

    public function channelable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Return the discounts relationship
     */
    public function discounts(): MorphToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->morphedByMany(
            Discount::class,
            'channelable',
            "{$prefix}channelables"
        );
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

    /**
     * Return the products relationship
     */
    public function collections(): MorphToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->morphedByMany(
            Collection::class,
            'channelable',
            "{$prefix}channelables"
        );
    }

    /**
     * Whether any order has been placed against this channel. Used to gate
     * hard deletion in the admin — channels with order history should be
     * marked Inactive, not deleted, so historical orders keep their context.
     */
    public function hasOrderHistory(): bool
    {
        return Order::query()->where('channel_id', $this->id)->exists();
    }
}
