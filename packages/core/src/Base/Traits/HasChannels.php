<?php

namespace Lunar\Base\Traits;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use Lunar\Models\Channel;

trait HasChannels
{
    public static function bootHasChannels()
    {
        static::created(function (Model $model) {
            // Add our initial channels, set to not be enabled or scheduled.
            $channels = Channel::get()->mapWithKeys(function ($channel) {
                return [
                    $channel->id => [
                        'enabled' => false,
                        'starts_at' => null,
                        'ends_at' => null,
                    ],
                ];
            });

            $model->channels()->sync($channels);
        });
    }

    /**
     * Get all of the models channels.
     */
    public function channels()
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->morphToMany(
            Channel::class,
            'channelable',
            "{$prefix}channelables"
        )->withPivot([
            'enabled',
            'starts_at',
            'ends_at',
        ])->withTimestamps();
    }

    public function scheduleChannel($channel, DateTime $startsAt = null, DateTime $endsAt = null)
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
     * @param Builder $query
     * @param Channel|int $channel
     *
     * @return Builder
     */
    public function scopeChannel($query, $channel = null, DateTime $startsAt = null, DateTime $endsAt = null)
    {
        if (!$channel) {
            return $query;
        }

        if (!$startsAt) {
            $startsAt = now();
        }

        if (!$endsAt) {
            $endsAt = now()->addSecond();
        }

        if (is_a($channel, Channel::class)) {
            $channel = collect([$channel]);
        }

        return $query->whereHas('channels', function ($relation) use ($channel, $startsAt, $endsAt) {
            $relation->whereIn(
                $this->channels()->getTable() . '.channel_id',
                $channel->pluck('id')
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
