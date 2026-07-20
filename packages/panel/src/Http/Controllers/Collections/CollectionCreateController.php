<?php

namespace Lunar\Panel\Http\Controllers\Collections;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Collections\CreatesChildCollection;
use Lunar\Core\Contracts\Actions\Collections\CreatesRootCollection;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Panel\Http\Requests\Collections\CollectionStoreRequest;

class CollectionCreateController
{
    public function create(Request $request): Response
    {
        // The list page's per-group and per-row add affordances preselect
        // where the new collection sits.
        $parent = $request->filled('parent')
            ? Collection::query()->find($request->integer('parent'))
            : null;

        return Inertia::render('collections/Create', [
            'groups' => CollectionGroup::query()->orderBy('name')->get(['id', 'name']),
            'preselected' => [
                'group_id' => $parent?->collection_group_id
                    ?? ($request->filled('group') ? $request->integer('group') : null),
                'parent' => $parent ? [
                    'id' => $parent->id,
                    'name' => $parent->translate('name'),
                    'breadcrumb' => $parent->breadcrumb->implode(' > '),
                ] : null,
            ],
            'urls' => [
                'store' => route('panel.collections.store'),
                'index' => route('panel.collections.index'),
                'collectionsSearch' => route('panel.catalog.collections.search'),
            ],
        ]);
    }

    public function store(
        CollectionStoreRequest $request,
        CreatesRootCollection $createsRootCollection,
        CreatesChildCollection $createsChildCollection,
    ): RedirectResponse {
        $parent = $request->parent();

        $collection = $parent
            ? $createsChildCollection->execute($parent, $request->validated()['name'], $request->collectionAttributes())
            : $createsRootCollection->execute(
                (int) $request->validated()['collection_group_id'],
                $request->validated()['name'],
                $request->collectionAttributes(),
            );

        return redirect()
            ->route('panel.collections.edit', $collection)
            ->with('success', __('panel::collections.flash_created'));
    }
}
