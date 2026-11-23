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
     * Remove the targets from the product's associations of this type, or of
     * every type when none is given, through the sync action.
     */
    public function handle(): void
    {
        $targetIds = $this->targets->map(fn ($model) => (int) $model->id)->all();

        $types = $this->type
            ? [is_string($this->type) ? $this->type : $this->type->value]
            : $this->product->associations()->reorder()->distinct()->pluck('type')->map(fn ($type) => (string) $type)->all();

        DB::transaction(function () use ($types, $targetIds): void {
            foreach ($types as $type) {
                $remaining = $this->product->associations()
                    ->where('type', $type)
                    ->pluck('product_target_id')
                    ->map(fn ($id) => (int) $id)
                    ->reject(fn (int $id): bool => in_array($id, $targetIds, true))
                    ->values()
                    ->all();

                $this->product->syncAssociations($type, $remaining);
            }
        });
    }
}
