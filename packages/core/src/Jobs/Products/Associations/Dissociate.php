<?php

namespace Lunar\Jobs\Products\Associations;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Lunar\Base\BaseModel;
use Lunar\Base\Enums\Concerns\ProvidesProductAssociationType;
use Lunar\Facades\DB;
use Lunar\Models\Contracts\Product as ProductContract;
use Lunar\Models\Product;

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
    protected ProductContract $product;

    /**
     * The SKU for the generated variant.
     *
     * @var string
     */
    protected $type = null;

    /**
     * Create a new job instance.
     */
    public function __construct(ProductContract $product, Collection|BaseModel|array $targets, ProvidesProductAssociationType|string|null $type = null)
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
            $query = $this->product->associations()->whereIn(
                'product_target_id',
                $this->targets->pluck('id')
            )->when(
                $this->type,
                fn ($query) => $query->where(
                    'type',
                    is_string($this->type) ? $this->type : $this->type->value
                )
            );

            $query->delete();
        });
    }
}
