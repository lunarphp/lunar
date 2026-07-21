<?php

namespace Lunar\Panel\Http\Controllers\Collections;

use Illuminate\Http\Request;
use Illuminate\Support\Collection as SupportCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\States\Collection\Archived;
use Lunar\Core\States\Collection\Draft;
use Lunar\Core\States\Collection\Published;
use Lunar\Panel\Http\Controllers\Concerns\PresentsCollectionRows;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class CollectionIndexController
{
    use PresentsCollectionRows;
    use ResolvesTableExtensions;

    public function index(Request $request): Response
    {
        $resolver = $this->resolveTable('collections.index');

        $term = trim($request->string('q')->value());
        $status = $request->string('status')->value();
        $status = in_array($status, [Published::$name, Draft::$name, Archived::$name], true) ? $status : null;
        $filtering = $term !== '' || $status !== null;

        // When filtering, the visible set is every match plus its ancestors so
        // each match stays reachable inside the tree — the rest is omitted.
        $matchedIds = null;
        $visibleIds = null;

        if ($filtering) {
            $matched = Collection::query()
                ->when($term !== '', function ($query) use ($term, $resolver) {
                    $like = "%{$term}%";

                    $query->where(function ($query) use ($like, $term, $resolver) {
                        // The dedicated name column holds a {locale: text} map.
                        $query->where('name', 'like', $like)
                            ->orWhere('handle', 'like', $like)
                            ->orWhereHas('urls', fn ($query) => $query->where('slug', 'like', $like));

                        $resolver->applySearchQueries($query, $term);
                    });
                })
                ->when($status !== null, fn ($query) => $query->where('status', $status))
                ->tap(fn ($query) => $resolver->applyFilters($query, $request))
                ->pluck('id');

            $parents = Collection::query()->pluck('parent_id', 'id');

            $visible = [];

            foreach ($matched as $id) {
                $cursor = $id;

                while ($cursor !== null && ! isset($visible[$cursor])) {
                    $visible[$cursor] = true;
                    $cursor = $parents[$cursor] ?? null;
                }
            }

            $matchedIds = $matched->flip();
            $visibleIds = $visible;
        }

        // Browse mode ships only root collections; each subtree is fetched on
        // demand as rows are expanded. Filtering keeps the eager set (matches
        // plus their ancestors) so deep matches stay reachable.
        $collections = Collection::query()
            ->withCount('products')
            ->with('thumbnail')
            ->when(! $filtering, fn ($query) => $query->whereNull('parent_id'))
            ->when($visibleIds !== null, fn ($query) => $query->whereIn('id', array_keys($visibleIds)))
            ->orderBy('collection_group_id')
            ->orderBy('_lft')
            ->get();

        $rows = $collections->map(fn (Collection $collection) => $this->collectionRow($collection, $resolver, $matchedIds));

        $groups = CollectionGroup::query()
            ->withCount('collections')
            ->orderBy('name')
            ->get()
            ->map(fn (CollectionGroup $group) => [
                'id' => $group->id,
                'name' => $group->name,
                'handle' => $group->handle,
                'collections_count' => (int) $group->getAttribute('collections_count'),
                'tree' => $this->buildTree($rows->where('group_id', $group->id)->values()),
                'urls' => [
                    'update' => route('panel.collections.groups.update', $group),
                    'destroy' => route('panel.collections.groups.destroy', $group),
                    'create_collection' => route('panel.collections.create', ['group' => $group->id]),
                ],
            ])
            ->values();

        $totalCount = Collection::count();

        return Inertia::render('collections/Index', [
            'groups' => $groups,
            'tableActions' => $resolver->getActions(),
            'filtering' => $filtering,
            'matchedCount' => $filtering ? ($matchedIds?->count() ?? 0) : $totalCount,
            'totalCount' => $totalCount,
            'filters' => $request->only(['q', 'status']),
            'urls' => [
                'index' => route('panel.collections.index'),
                'create' => route('panel.collections.create'),
                'groupsStore' => route('panel.collections.groups.store'),
            ],
        ]);
    }

    /**
     * Nest the flat, _lft-ordered rows of one group into a children tree.
     * Every row's parent is guaranteed present: browse payloads carry only
     * roots (nothing to nest), filtered ones include every match's ancestors.
     *
     * @param  SupportCollection<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    protected function buildTree(SupportCollection $rows): array
    {
        $byId = [];

        foreach ($rows as $row) {
            $byId[$row['id']] = $row;
        }

        $roots = [];

        foreach ($byId as $id => &$row) {
            if ($row['parent_id'] !== null && isset($byId[$row['parent_id']])) {
                $byId[$row['parent_id']]['children'][] = &$row;
            } else {
                $roots[] = &$row;
            }
        }

        return $roots;
    }
}
