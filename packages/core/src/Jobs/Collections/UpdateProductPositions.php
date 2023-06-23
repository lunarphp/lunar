<?php

namespace Lunar\Jobs\Collections;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Lunar\Actions\Collections\SortProducts;
use Lunar\Facades\DB;
use Lunar\Models\Collection;

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
            $products = app(SortProducts::class)->execute($this->collection);
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
