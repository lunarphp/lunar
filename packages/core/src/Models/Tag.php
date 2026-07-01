<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lunar\Core\Database\Factories\TagFactory;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property string $public_id
 * @property string $value
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Tag extends Base
{
    use HasFactory;
    use HasMacros;
    use HasPublicId;
    use LogsActivity;

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

    protected function value(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value === null ? null : Str::upper($value),
        );
    }
}
