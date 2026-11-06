<?php

namespace Lunar\Bundles\Panel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Lunar\Bundles\Contracts\Actions\DefinesBundle;
use Lunar\Bundles\Contracts\Actions\DeletesBundle;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Panel\BundleSummary;
use Lunar\Bundles\Panel\Http\Controllers\Concerns\TranslatesBundleExceptions;
use Lunar\Bundles\Panel\Http\Requests\DefineBundleRequest;
use Lunar\Core\Models\ProductVariant;

class BundleController
{
    use TranslatesBundleExceptions;

    public function show(ProductVariant $variant, BundleSummary $summary): JsonResponse
    {
        return response()->json($summary->for($variant));
    }

    public function update(DefineBundleRequest $request, ProductVariant $variant, DefinesBundle $define, BundleSummary $summary): JsonResponse
    {
        $discount = $request->input('discount_percentage');

        $this->guarded('bundle', fn () => $define->execute(
            $variant,
            BundlePricing::from($request->string('pricing')->value()),
            $discount === null || $discount === '' ? null : (float) $discount,
        ));

        $variant->unsetRelation('bundle');

        return response()->json($summary->for($variant));
    }

    public function destroy(ProductVariant $variant, DeletesBundle $delete, BundleSummary $summary): JsonResponse
    {
        $variant->loadMissing('bundle');

        if ($variant->bundle) {
            $delete->execute($variant->bundle);
            $variant->unsetRelation('bundle');
        }

        return response()->json($summary->for($variant));
    }
}
