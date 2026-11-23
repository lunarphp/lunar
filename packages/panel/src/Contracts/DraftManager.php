<?php

namespace Lunar\Panel\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Lunar\Panel\Drafts\CommitResult;
use Lunar\Panel\Drafts\StaleDraftException;
use Lunar\Panel\Models\EditDraft;

interface DraftManager
{
    public function find(Model $draftable, Authenticatable $staff): ?EditDraft;

    /**
     * Upsert the staff member's draft, replacing `data` wholesale with the
     * incoming diff: base snapshots are captured for newly-present keys and
     * dropped for keys no longer present. An empty diff deletes the draft
     * (the form returned to clean) and returns null.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $base  field key to the value the client showed before editing it; the snapshot for a newly drafted key
     * @param  int|null  $version  the draft version the client last saw; null skips the check
     *
     * @throws ValidationException
     * @throws StaleDraftException
     */
    public function merge(DraftableResource $resource, Model $draftable, Authenticatable $staff, array $data, array $base = [], ?int $version = null): ?EditDraft;

    /**
     * @throws StaleDraftException
     */
    public function discard(Model $draftable, Authenticatable $staff, ?int $version = null): void;

    /**
     * Attempt to commit the client's diff, which replaces the stored draft:
     * apply any snapshot rebases from conflict resolution, validate the full
     * payload, then detect per-field conflicts against the record as locked
     * inside the commit transaction. Applies everything or nothing — any
     * conflict means no field is written.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rebase  field key to the current-DB value the client resolved against
     * @param  array<string, mixed>  $base  as for merge()
     *
     * @throws ValidationException
     * @throws StaleDraftException
     */
    public function commit(DraftableResource $resource, Model $draftable, Authenticatable $staff, array $data, array $rebase = [], array $base = [], ?int $version = null): CommitResult;
}
