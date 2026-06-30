<?php

namespace Lunar\Core\Jobs\Products\Associations;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Lunar\Core\Enums\Concerns\ProvidesProductAssociationType;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Base;
use Lunar\Core\Models\Product;

class Dissociate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $tries = 1;

    /**
     * The product or collection of products to be associated.
     *
     * @var mixed
     */
    protected $targets;

    /**
     * The parent product instance.
     */
    protected Product $product;

    /**
     * The SKU for the generated variant.
     *
     * @var string
     */
    protected $type = null;

    /**
     * Create a new job instance.
     */
    public function __construct(Product $product, Collection|Base|array $targets, ProvidesProductAssociationType|string|null $type = null)
    {
        if (is_array($targets)) {
            $targets = collect($targets);
        }

        if (! $targets instanceof Collection) {
            $targets = collect([$targets]);
        }

        $this->product = $product;
        $this->targets = $targets;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function () {
            $associations = $this->product->associations()->whereIn(
                'product_target_id',
                $this->targets->pluck('id')
            )->when(
                $this->type,
                fn ($query) => $query->where(
                    'type',
                    is_string($this->type) ? $this->type : $this->type->value
                )
            )->get();

            // Delete per-model so the deleted event fires and cache invalidation
            // (both products) cascades; a bulk delete() would bypass it.
            $associations->each->delete();
        });
    }
}
