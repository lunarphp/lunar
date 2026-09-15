<?php

namespace Lunar\Bundles\Panel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Lunar\Bundles\Panel\BundleSummary;
use Lunar\Bundles\Panel\Http\Controllers\Concerns\TranslatesBundleExceptions;
use Lunar\Bundles\Panel\Http\Requests\SyncBundleGroupsRequest;
use Lunar\Core\Models\ProductVariant;

class BundleGroupsController
{
    use TranslatesBundleExceptions;

    public function update(SyncBundleGroupsRequest $request, ProductVariant $variant, BundleSummary $summary): JsonResponse
    {
        $bundle = $request->bundle($variant);

        $groups = collect($request->validated('groups', []))
            ->values()
            ->map(fn (array $group, int $index) => array_filter([
                'id' => isset($group['id']) ? (int) $group['id'] : null,
                'name' => array_filter($group['name'], fn ($value) => $value !== null && $value !== ''),
                'min_selections' => (int) $group['min_selections'],
                'max_selections' => (int) $group['max_selections'],
                'position' => (int) ($group['position'] ?? $index),
            ], fn ($value) => $value !== null))
            ->all();

        $this->guarded('groups', fn () => $bundle->syncGroups($groups));

        $variant->unsetRelation('bundle');

        return response()->json($summary->for($variant));
    }
}
