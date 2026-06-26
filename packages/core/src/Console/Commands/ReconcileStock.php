<?php

namespace Lunar\Core\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Core\Contracts\Actions\Products\RecomputesStockReserved;
use Lunar\Core\Contracts\Actions\Products\SyncsStockCommitment;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockLevel;
use Lunar\Core\Models\StockMovement;

class ReconcileStock extends Command
{
    protected $signature = 'lunar:stock:reconcile
                            {--variant=* : Limit to specific product variant IDs}';

    protected $description = 'Rebuild on_hand from the movement ledger and committed from open orders, then refresh the rollup.';

    public function handle(SyncsStockCommitment $syncCommitment, RecomputesStockReserved $recomputeReserved): int
    {
        ProductVariant::query()
            ->when($this->option('variant'), fn ($query, $ids) => $query->whereIn('id', $ids))
            ->chunkById(100, function ($variants) use ($syncCommitment, $recomputeReserved) {
                foreach ($variants as $variant) {
                    $this->rebuildOnHand($variant);
                    $recomputeReserved->execute($variant);
                    $syncCommitment->execute($variant);
                }
            });

        $this->info('Stock reconciled.');

        return self::SUCCESS;
    }

    /**
     * Rebuild each location's `on_hand` as the running sum of its ledger.
     */
    private function rebuildOnHand(ProductVariant $variant): void
    {
        $sums = StockMovement::query()
            ->where('product_variant_id', $variant->getKey())
            ->selectRaw('location_id, COALESCE(SUM(quantity), 0) as on_hand')
            ->groupBy('location_id')
            ->get();

        foreach ($sums as $row) {
            StockLevel::query()->updateOrCreate(
                ['product_variant_id' => $variant->getKey(), 'location_id' => $row->location_id],
                ['on_hand' => (int) $row->on_hand],
            );
        }
    }
}
