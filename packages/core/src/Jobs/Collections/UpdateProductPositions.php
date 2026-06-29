<?php

namespace Lunar\Core\Jobs\Collections;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Contracts\Actions\Collections\SortsProducts;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Collection;

class UpdateProductPositions implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $tries = 1;

    /**
     * The target collection.
     */
    public Collection $collection;

    /**
     * Create a new job instance.
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function handle()
    {
        if ($this->collection->sort == 'custom') {
            return;
        }

        DB::transaction(function () {
            $products = app(SortsProducts::class)->execute($this->collection);
            $productSync = $products->values()->mapWithKeys(function ($product, $index) {
                return [
                    $product->id => [
                        'position' => $index + 1,
                    ],
                ];
            });
            $this->collection->products()->sync($productSync);
        });
    }
}
