<?php

namespace Lunar\Panel\Http\Controllers\Collections;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Collections\MovesCollection;
use Lunar\Core\Exceptions\CollectionActionException;
use Lunar\Core\Models\Collection;
use Lunar\Panel\Http\Requests\Collections\CollectionMoveRequest;

/**
 * Hierarchy moves apply immediately rather than through the edit draft:
 * a re-parent restructures a tree other staff are looking at, and stale
 * draft state could commit a move into a node that has since moved itself.
 */
class CollectionMoveController
{
    public function update(CollectionMoveRequest $request, Collection $collection, MovesCollection $movesCollection): RedirectResponse
    {
        try {
            $movesCollection->execute($collection, $request->parent(), $request->group());
        } catch (CollectionActionException) {
            return back()->with('error', __('panel::collections.flash_move_invalid'));
        }

        return back()->with('success', __('panel::collections.flash_moved'));
    }
}
