<?php

namespace Lunar\Panel\Drafts;

use Illuminate\Database\Eloquent\Model;
use Lunar\Panel\Contracts\DraftSlice as DraftSliceContract;
use Lunar\Panel\Forms\FormSlice;
use Lunar\Panel\Models\EditDraft;

abstract class DraftSlice extends FormSlice implements DraftSliceContract
{
    public function discard(Model $record, EditDraft $draft): void {}
}
