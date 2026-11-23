<?php

namespace Lunar\Panel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Lunar\Core\Models\Base;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Database\Factories\EditDraftFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A staff member's in-progress edit of one record: `data` holds the changed
 * field values, `base_snapshot` the database value each field had when it
 * first entered the draft, and `version` counts its writes so a tab holding
 * an older copy is refused rather than overwriting it. One draft per staff
 * member per record.
 *
 * Uploads made while editing are staged on the draft as its media, under the
 * record's collection names, so nothing outside the draft sees them until a
 * commit moves them onto the record. Deleting a draft (discard, prune)
 * deletes its staged media; a commit deletes the draft quietly so the
 * commit can still move them.
 */
class EditDraft extends Base implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use Prunable {
        pruneAll as protected pruneExpired;
    }

    /** Staged media this old that no draft lists any more are swept. */
    public const STAGED_MEDIA_GRACE_MINUTES = 60;

    /** @var array<int, string> */
    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = [
        'data' => 'array',
        'base_snapshot' => 'array',
        'version' => 'integer',
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
     * resume. Pruned one model at a time so each fires its deleting event and
     * the slice discard fan-out runs.
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

    /**
     * Prune expired drafts, then sweep staged media nothing will commit:
     * uploads whose draft is gone (a commit that failed after its
     * transaction) and uploads a live draft no longer lists (removed before
     * Save, or a revert that autosaved the media keys away). The grace period
     * spares an upload whose list has not autosaved yet.
     */
    public function pruneAll(int $chunkSize = 1000): int
    {
        $total = (int) $this->pruneExpired($chunkSize);

        $cutoff = now()->subMinutes(self::STAGED_MEDIA_GRACE_MINUTES);
        $drafts = static::query()->pluck('data', 'id');

        Media::query()
            ->where('model_type', $this->getMorphClass())
            ->where('created_at', '<', $cutoff)
            ->each(function (Media $media) use ($drafts): void {
                $data = $drafts->get($media->model_id);

                if ($data === null || ! in_array((int) $media->id, static::listedMediaIds((array) $data), true)) {
                    $media->delete();
                }
            });

        return $total;
    }

    /**
     * Every media id a draft's `media:*` keys list.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, int>
     */
    public static function listedMediaIds(array $data): array
    {
        $ids = [];

        foreach ($data as $key => $rows) {
            if (str_starts_with((string) $key, 'media:') && is_array($rows)) {
                foreach ($rows as $row) {
                    if (is_array($row) && isset($row['id'])) {
                        $ids[] = (int) $row['id'];
                    }
                }
            }
        }

        return $ids;
    }
}
