<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\TagFactory;
use Lunar\Facades\DB;

/**
 * @property int $id
 * @property string $value
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class Tag extends BaseModel implements Contracts\Tag
{
    use HasFactory;
    use HasMacros;

    public static function booted(): void
    {
        static::deleting(function (self $tag) {
            DB::table(config('lunar.database.table_prefix').'taggables')
                ->where('tag_id', $tag->id)
                ->delete();
        });
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return TagFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }
}
