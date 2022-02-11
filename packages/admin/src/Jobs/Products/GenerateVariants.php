<?php

namespace GetCandy\Hub\Jobs\Products;

use GetCandy\Hub\Exceptions\InvalidProductValuesException;
use GetCandy\Models\Product;
use GetCandy\Models\ProductOptionValue;
use GetCandy\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GenerateVariants implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $tries = 1;

    /**
     * The product instance.
     *
     * @var \GetCandy\Models\Product
     */
    protected $product;

    /**
     * The option values to use to generate variants.
     *
     * @var array
     */
    protected array $optionValues;

    protected bool $isAdditional = false;

    /**
     * Create a new job instance.
     *
     * @param  \GetCandy\Models\Product  $product
     * @param  iterable  $optionValues
     * @return void
     */
    public function __construct(Product $product, iterable $optionValues, $additional = false)
    {
        $this->product = $product;

        if ($optionValues instanceof Collection) {
            $optionValues = $optionValues->toArray();
        }

        $this->optionValues = $optionValues;
        $this->isAdditional = $additional;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Validator::make([
            'optionValues' => $this->optionValues,
        ], [
            'optionValues'   => 'array',
            'optionValues.*' => 'numeric',
        ])->validate();

        $valueModels = ProductOptionValue::findMany($this->optionValues);

        if ($valueModels->count() != count($this->optionValues)) {
            throw new InvalidProductValuesException(
                'One or more option values do not exist in the database'
            );
        }

        $permutations = $this->getPermutations();

        $baseVariant = $this->product->variants->first();

        DB::transaction(function () use ($permutations, $baseVariant) {
            // Validation bits
            $rules = config('getcandy-hub.products', []);

            foreach ($permutations as $key => $optionsToCreate) {
                $variant = new ProductVariant();

                $uoms = ['length', 'width', 'height', 'weight', 'volume'];

                $attributesToCopy = [
                    'sku',
                    'gtin',
                    'mpn',
                    'ean',
                    'shippable',
                ];

                foreach ($uoms as $uom) {
                    $attributesToCopy[] = "{$uom}_value";
                    $attributesToCopy[] = "{$uom}_unit";
                }

                $attributes = $baseVariant->only($attributesToCopy);

                foreach ($attributes as $attribute => $value) {
                    if ($rules[$attribute]['unique'] ?? false) {
                        $attributes[$attribute] = $attributes[$attribute].'-'.($key + 1);
                    }
                }

                $pricing = $baseVariant->prices->map(function ($price) {
                    return $price->only([
                        'customer_group_id',
                        'currency_id',
                        'price',
                        'compare_price',
                        'tier',
                    ]);
                });

                $variant->product_id = $baseVariant->product_id;
                $variant->tax_class_id = $baseVariant->tax_class_id;
                $variant->attribute_data = $baseVariant->attribute_data;
                $variant->fill($attributes);
                $variant->save();
                $variant->values()->attach($optionsToCreate);
                $variant->prices()->createMany($pricing->toArray());
            }

            if ($baseVariant && ! $this->isAdditional) {
                $baseVariant->values()->detach();
                $baseVariant->prices()->delete();
                $baseVariant->delete();
            }
        });
    }

    /**
     * Gets permutations array from the option values.
     *
     * @return array
     */
    protected function getPermutations()
    {
        return Arr::permutate(
            ProductOptionValue::findMany($this->optionValues)
                ->groupBy('product_option_id')
                ->mapWithKeys(function ($values) {
                    $optionId = $values->first()->product_option_id;

                    return [$optionId => $values->map(function ($value) {
                        return $value->id;
                    })];
                })->toArray()
        );
    }
}
