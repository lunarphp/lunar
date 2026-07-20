<?php

namespace Lunar\Panel\Http\Controllers\Collections;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\CollectionGroups\CreatesCollectionGroup;
use Lunar\Core\Contracts\Actions\CollectionGroups\DeletesCollectionGroup;
use Lunar\Core\Contracts\Actions\CollectionGroups\UpdatesCollectionGroup;
use Lunar\Core\Exceptions\CollectionGroupActionException;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Panel\Http\Requests\Collections\CollectionGroupRequest;

class CollectionGroupController
{
    public function store(CollectionGroupRequest $request, CreatesCollectionGroup $createsCollectionGroup): RedirectResponse
    {
        $createsCollectionGroup->execute($request->groupAttributes());

        return back()->with('success', __('panel::collections.flash_group_created'));
    }

    public function update(CollectionGroupRequest $request, CollectionGroup $collectionGroup, UpdatesCollectionGroup $updatesCollectionGroup): RedirectResponse
    {
        $updatesCollectionGroup->execute($collectionGroup, $request->groupAttributes());

        return back()->with('success', __('panel::collections.flash_group_updated'));
    }

    public function destroy(CollectionGroup $collectionGroup, DeletesCollectionGroup $deletesCollectionGroup): RedirectResponse
    {
        try {
            $deletesCollectionGroup->execute($collectionGroup);
        } catch (CollectionGroupActionException) {
            return back()->with('error', __('panel::collections.flash_group_delete_protected'));
        }

        return back()->with('success', __('panel::collections.flash_group_deleted'));
    }
}
