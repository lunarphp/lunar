<?php

namespace Lunar\Base\Traits;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Lunar\Facades\DB;
use Lunar\Models\Channel;
use Lunar\Models\Contracts\Channel as ChannelContract;

trait HasChannels
{
    public static function bootHasChannels()
    {
        static::created(function (Model $model) {
            // Add our initial channels, set to not be enabled or scheduled.
            $channels = Channel::get()->mapWithKeys(function ($channel) {
                return [
                    $channel->id => [
                        'enabled' => $channel->default,
                        'starts_at' => $channel->default ? now() : null,
                        'ends_at' => null,
                    ],
                ];
            });

            $model->channels()->sync($channels);
        });
    }

    /**
     * Get all of the models channels.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<Channel>
     */
    public function channels()
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->morphToMany(
            Channel::class,
            'channelable',
            "{$prefix}channelables",
        )->withPivot([
            'enabled',
            'starts_at',
            'ends_at',
        ])->withTimestamps();
    }

    public function scheduleChannel($channel, ?DateTime $startsAt = null, ?DateTime $endsAt = null)
    {
        if ($channel instanceof Model) {
            $channel = collect([$channel]);
        }

        DB::transaction(function () use ($channel, $startsAt, $endsAt) {
            $this->channels()->sync(
                $channel->mapWithKeys(function ($channel) use ($startsAt, $endsAt) {
                    return [
                        $channel->id => [
                            'enabled' => true,
                            'starts_at' => $startsAt,
                            'ends_at' => $endsAt,
                        ],
                    ];
                })
            );
        });
    }

    /**
     * Return the active channels relationship.
     *
     * @return MorphToMany
     */
    public function activeChannels()
    {
        return $this->channels()->where(function ($query) {
            $query->whereNull('starts_at')
                ->orWhere('starts_at', '<=', now());
        })->where(function ($query) {
            $query->whereNull('ends_at')
                ->orWhere('ends_at', '>=', now());
        })->whereEnabled(true);
    }

    /**
     * Apply the channel scope to the query
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeChannel($query, ChannelContract|iterable|null $channel = null, ?DateTime $startsAt = null, ?DateTime $endsAt = null)
    {
        if (blank($channel)) {
            return $query;
        }

        if (! $startsAt) {
            $startsAt = now();
        }

        if (! $endsAt) {
            $endsAt = now()->addSecond();
        }

        $channelIds = collect();

        if (is_a($channel, Channel::modelClass())) {
            $channelIds = collect([$channel->id]);
        }

        if (is_a($channel, Collection::class)) {
            $channelIds = $channel->pluck('id');
        }

        if (is_array($channel)) {
            $channelIds = collect($channel)->pluck('id');
        }

        return $query->whereHas('channels', function ($relation) use ($channelIds, $startsAt, $endsAt) {
            $relation->whereIn(
                $this->channels()->getTable().'.channel_id',
                $channelIds
            )->where(function ($query) use ($startsAt) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $startsAt);
            })->where(function ($query) use ($endsAt) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $endsAt);
            })->whereEnabled(true);
        });
    }
}
