<?php

namespace Lunar\Panel\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Lunar\Panel\Drafts\CommitResult;
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
     */
    public function merge(DraftableResource $resource, Model $draftable, Authenticatable $staff, array $data): ?EditDraft;

    public function discard(Model $draftable, Authenticatable $staff): void;

    /**
     * Attempt to commit: overlay the request's final diff onto the stored
     * draft, apply any snapshot rebases from conflict resolution, validate
     * the full payload, then detect per-field conflicts. Applies everything
     * or nothing — any conflict means no field is written.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rebase  field key to the current-DB value the client resolved against
     *
     * @throws ValidationException
     */
    public function commit(DraftableResource $resource, Model $draftable, Authenticatable $staff, array $data, array $rebase = []): CommitResult;
}
