<?php

namespace Lunar\Bundles\Panel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Lunar\Bundles\Panel\BundleSummary;
use Lunar\Bundles\Panel\Http\Controllers\Concerns\TranslatesBundleExceptions;
use Lunar\Bundles\Panel\Http\Requests\SyncBundleComponentsRequest;
use Lunar\Core\Models\ProductVariant;

class BundleComponentsController
{
    use TranslatesBundleExceptions;

    public function update(SyncBundleComponentsRequest $request, ProductVariant $variant, BundleSummary $summary): JsonResponse
    {
        $bundle = $request->bundle($variant);

        $components = collect($request->validated('components', []))
            ->values()
            ->map(fn (array $component, int $index) => [
                'variant' => (int) $component['variant_id'],
                'quantity' => (int) ($component['quantity'] ?? 1),
                'group' => isset($component['group_id']) ? (int) $component['group_id'] : null,
                'default' => (bool) ($component['default'] ?? false),
                'position' => (int) ($component['position'] ?? $index),
            ])
            ->all();

        $this->guarded('components', fn () => $bundle->syncComponents($components));

        $variant->unsetRelation('bundle');

        return response()->json($summary->for($variant));
    }
}
