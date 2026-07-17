<?php

namespace Lunar\Panel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Lunar\Core\Models\Base;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Database\Factories\EditDraftFactory;

/**
 * A staff member's in-progress edit of one record: `data` holds the changed
 * field values, `base_snapshot` the database value each field had when it
 * first entered the draft. One draft per staff member per record.
 */
class EditDraft extends Base
{
    use HasFactory;
    use MassPrunable;

    /** @var array<int, string> */
    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = [
        'data' => 'array',
        'base_snapshot' => 'array',
    ];

    protected static function newFactory(): EditDraftFactory
    {
        return EditDraftFactory::new();
    }

    public function draftable(): MorphTo
    {
        return $this->morphTo();
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Drafts untouched beyond the configured TTL: their base snapshots are too
     * stale for trustworthy conflict comparison, so they prune rather than
     * resume.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where(
            'updated_at',
            '<',
            now()->subDays((int) config('lunar.panel.drafts.ttl_days', 7)),
        );
    }
}
