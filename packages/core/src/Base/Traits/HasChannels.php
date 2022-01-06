<?php

namespace GetCandy\Base\Traits;

use DateTime;
use GetCandy\Models\Channel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

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
                        'published_at' => null,
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
        $prefix = config('getcandy.database.table_prefix');

        return $this->morphToMany(
            Channel::class,
            'channelable',
            "{$prefix}channelables"
        )->withPivot([
            'enabled',
            'published_at',
        ])->withTimestamps();
    }

    public function scheduleChannel($channel, DateTime $date = null)
    {
        if ($channel instanceof Model) {
            $channel = collect([$channel]);
        }

        DB::transaction(function () use ($channel, $date) {
            $this->channels()->sync(
                $channel->mapWithKeys(function ($channel) use ($date) {
                    return [
                        $channel->id => [
                            'enabled' => true,
                            'published_at' => $date,
                        ],
                    ];
                })
            );
        });
    }
}
