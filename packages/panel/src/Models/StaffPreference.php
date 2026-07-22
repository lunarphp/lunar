<?php

namespace Lunar\Panel\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Models\Base;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Database\Factories\StaffPreferenceFactory;

/**
 * One per-staff panel preference (the dashboard layout, and whatever
 * follows), stored as a JSON value under a string key.
 *
 * @property int $id
 * @property int $staff_id
 * @property string $key
 * @property array<string, mixed> $value
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class StaffPreference extends Base
{
    use HasFactory;

    /** @var array<int, string> */
    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = [
        'value' => 'array',
    ];

    protected static function newFactory(): StaffPreferenceFactory
    {
        return StaffPreferenceFactory::new();
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /** @return array<string, mixed>|null */
    public static function valueFor(Staff $staff, string $key): ?array
    {
        return static::query()
            ->where('staff_id', $staff->id)
            ->where('key', $key)
            ->value('value');
    }

    /** @param array<string, mixed> $value */
    public static function put(Staff $staff, string $key, array $value): void
    {
        static::query()->updateOrCreate(
            ['staff_id' => $staff->id, 'key' => $key],
            ['value' => $value],
        );
    }

    public static function forget(Staff $staff, string $key): void
    {
        static::query()
            ->where('staff_id', $staff->id)
            ->where('key', $key)
            ->delete();
    }
}
