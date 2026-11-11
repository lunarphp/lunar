<?php

namespace Lunar\Panel\Contracts;

use Illuminate\Database\Eloquent\Model;
use Lunar\Panel\Models\EditDraft;

/**
 * A form slice that also takes part in the draft lifecycle of a drafted edit
 * page. Every FormSlice already autosaves, restores and conflict-checks when
 * composed into a draft; this adds the hook a slice needs only when it keeps
 * state outside the draft's JSON columns (staged uploads, for instance).
 */
interface DraftSlice extends FormSlice
{
    /**
     * Called when a draft holding this slice's keys is discarded, pruned, or
     * orphaned by its record's deletion.
     */
    public function discard(Model $record, EditDraft $draft): void;
}
