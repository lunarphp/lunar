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
use Lunar\Core\Models\Product;

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
    protected Product $product;

    /**
     * The product association type.
     */
    protected ProvidesProductAssociationType|string $type;

    /**
     * Create a new job instance.
     */
    public function __construct(Product $product, mixed $targets, ProvidesProductAssociationType|string $type)
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
            $type = is_string($this->type) ? $this->type : $this->type->value;

            $sort = (int) $this->product->associations()->where('type', $type)->max('sort');

            $this->product->associations()->createMany(
                $this->targets->values()->map(function ($model, $index) use ($type, $sort) {
                    return [
                        'product_target_id' => $model->id,
                        'type' => $type,
                        'sort' => $sort + $index + 1,
                    ];
                })
            );
        });
    }
}
