<?php

namespace Lunar\Jobs\Products\Associations;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Lunar\Base\Enums\Concerns\ProvidesProductAssociationType;
use Lunar\Facades\DB;
use Lunar\Models\Contracts\Product as ProductContract;
use Lunar\Models\Product;

class Associate implements ShouldQueue
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
     * The product association type.
     */
    protected ProvidesProductAssociationType|string $type;

    /**
     * Create a new job instance.
     */
    public function __construct(ProductContract $product, mixed $targets, ProvidesProductAssociationType|string $type)
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
            $this->product->associations()->createMany(
                $this->targets->map(function ($model) {
                    return [
                        'product_target_id' => $model->id,
                        'type' => is_string($this->type) ? $this->type : $this->type->value,
                    ];
                })
            );
        });
    }
}
