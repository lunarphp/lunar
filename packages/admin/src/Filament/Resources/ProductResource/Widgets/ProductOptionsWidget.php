<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lunar\Admin\Support\Actions\Products\MapVariantsToProductOptions;
use Lunar\Facades\DB;
use Lunar\Models\Language;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Models\ProductVariant;

class ProductOptionsWidget extends BaseWidget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static string $view = 'lunarpanel::resources.product-resource.widgets.product-options';

    public ?Model $record;

    public array $variants = [];

    /**
     * The product options which are being actively configured.
     */
    public array $configuredOptions = [];

    public bool $configuringOptions = false;

    public function mount()
    {
        $this->configureBaseOptions();
    }

    public function addSharedOptionAction()
    {
        $existing = collect($this->configuredOptions)->pluck('id');

        return Action::make('addSharedOption')
            ->form([
                Select::make('product_option')
                    ->options(
                        fn () => ProductOption::whereNotIn('id', $existing)->get()
                            ->mapWithKeys(
                                fn ($option) => [$option->id => $option->translate('name')]
                            )
                    ),
            ])->action(function (array $data) {
                $productOption = ProductOption::with(['values'])->find($data['product_option']);
                $this->configuredOptions[] = $this->mapOption($productOption);
            });
    }

    public function configureBaseOptions(): void
    {
        $this->configuredOptions = $this->query()->get()->map(
            fn ($option) => $this->mapOption($option)
        )->toArray();

        $this->mapVariantPermutations(fillMissing: false);
    }

    public function cancelOptionConfiguring(): void
    {
        $this->configuringOptions = false;
        $this->configureBaseOptions();
    }

    public function query()
    {
        return $this->record->productOptions()
            ->with('values', function ($query) {
                $query->whereHas('variants', function ($relation) {
                    $relation->whereIn($relation->getModel()->getTable().'.id', $this->record->variants()->pluck('id'));
                });
            });
    }

    public function addRestrictedOption()
    {
        $this->configuredOptions[] = [
            'id' => null,
            'key' => Str::random(),
            'value' => '',
            'position' => count($this->configuredOptions) + 1,
            'readonly' => false,
            'option_values' => [
                [
                    'id' => null,
                    'key' => Str::random(),
                    'value' => '',
                    'position' => 1,
                    'readonly' => false,
                ],
            ],
        ];
    }

    public function updateConfiguredOptions()
    {
        $this->validate([
            'configuredOptions' => 'array|min:1',
            'configuredOptions.*.value' => 'required|string',
            'configuredOptions.*.option_values.*.value' => 'required|string',
        ]);
        $this->mapVariantPermutations();
        $this->configuringOptions = false;
    }

    public function removeVariant($key): void
    {
        unset($this->variants[$key]);
    }

    public function addOptionValue($path)
    {
        $option = $this->configuredOptions[$path];

        if ($option['readonly']) {
            return;
        }

        $this->configuredOptions[$path]['option_values'][] = [
            'key' => Str::random(),
            'value' => '',
            'position' => count($this->configuredOptions[$path]['option_values']) + 1,
            'readonly' => false,
        ];
    }

    public function removeOptionValue($index, $valueIndex)
    {
        if (! $index && ! is_numeric($index)) {
            unset($this->configuredOptions[$valueIndex]);
        } else {
            unset($this->configuredOptions[$index]['option_values'][$valueIndex]);
        }
    }

    public function mapVariantPermutations($fillMissing = true): void
    {
        $optionValues = collect($this->configuredOptions)
            ->filter(
                fn ($option) => $option['value']
            )
            ->mapWithKeys(
                fn ($option) => [$option['value'] => collect($option['option_values'])
                    ->map(
                        fn ($value) => $value['value']
                    )]
            )->toArray();

        $variants = $this->record->variants->load('values.option')->map(function ($variant) {
            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => $variant->basePrices->first()?->price->decimal ?: 0,
                'stock' => $variant->stock,
                'values' => $variant->values->mapWithKeys(
                    fn ($value) => [$value->option->translate('name') => $value->translate('name')]
                )->toArray(),
            ];
        })->toArray();

        $this->variants = MapVariantsToProductOptions::map($optionValues, $variants, $fillMissing);
    }

    protected function storeConfiguredOptions(): void
    {
        $language = Language::getDefault();
        /**
         * Go through our configured options and if they don't
         * exist in the database i.e. they are new, create and map them
         * so they are ready.
         */
        foreach ($this->configuredOptions as $optionIndex => $option) {
            if (empty($option['id'])) {
                $optionModel = ProductOption::create([
                    'name' => [
                        $language->code => $option['value'],
                    ],
                    'label' => [
                        $language->code => $option['value'],
                    ],
                    'handle' => Str::slug($option['value']),
                    'shared' => 0,
                ]);
                $this->configuredOptions[$optionIndex]['id'] = $optionModel->id;
            }

            foreach ($option['option_values'] as $optionValueIndex => $value) {
                if (empty($value['id'])) {
                    $optionValueModel = ProductOptionValue::create([
                        'product_option_id' => $this->configuredOptions[$optionIndex]['id'],
                        'name' => [
                            $language->code => $value['value'],
                        ],
                        'position' => $value['position'],
                    ]);
                    $this->configuredOptions[$optionIndex]['option_values'][$optionValueIndex]['id'] = $optionValueModel->id;
                }
            }
        }
    }

    protected function mapOptionValuesToIds(array $values): array
    {
        $valueIds = [];
        foreach ($values as $option => $value) {
            $configuredOption = collect(
                $this->configuredOptions
            )->first(
                fn ($o) => $o['value'] == $option
            );

            $valueId = collect($configuredOption['option_values'])->first(
                fn ($v) => $v['value'] == $value
            )['id'];
            $valueIds[] = $valueId;
        }

        return $valueIds;
    }

    public function saveVariantsAction()
    {
        return Action::make('saveVariants')
            ->action(function () {

                DB::beginTransaction();

                $this->storeConfiguredOptions();

                foreach ($this->variants as $variantIndex => $variantData) {
                    $variant = new ProductVariant([
                        'product_id' => $this->record->id,
                    ]);
                    $basePrice = null;

                    if (! empty($variantData['variant_id'])) {
                        $variant = ProductVariant::find($variantData['variant_id']);
                        $basePrice = $variant->basePrices->first();
                        $currency = $basePrice->currency;
                    }

                    if (! empty($variantData['copied_id'])) {
                        $copiedVariant = ProductVariant::find(
                            $variantData['copied_id']
                        );

                        $variant = $copiedVariant->replicate();
                        $variant->save();

                        $basePrice = $copiedVariant->basePrices->first()->replicate();
                        $basePrice->priceable_id = $variant->id;
                    }

                    $variant->sku = $variantData['sku'];
                    $variant->stock = $variantData['stock'];
                    $variant->save();

                    $basePrice->price = (int) bcmul($variantData['price'], $basePrice->currency->factor);
                    $basePrice->save();

                    $optionsValues = $this->mapOptionValuesToIds($variantData['values']);

                    $variant->values()->sync($optionsValues);

                    $this->variants[$variantIndex]['variant_id'] = $variant->id;
                }

                $productOptions = collect($this->configuredOptions)
                    ->mapWithKeys(function ($option) {
                        return [
                            $option['id'] => [
                                'position' => $option['position'],
                            ],
                        ];
                    });

                $this->record->productOptions()->sync($productOptions);

                $variantIds = collect($this->variants)->pluck('variant_id');

                $this->record->variants()->whereNotIn('id', $variantIds)
                    ->get()
                    ->each(
                        fn ($variant) => $variant->delete()
                    );
                DB::commit();

                Notification::make()->title(
                    'Product Variants Updated'
                )->success()->send();
            });
    }

    protected function mapOption(ProductOption $option): array
    {
        return [
            'id' => $option->id,
            'key' => Str::random(),
            'value' => $option->translate('name'),
            'position' => $option->pivot?->position ?: count($this->configuredOptions) + 1,
            'readonly' => $option->shared,
            'option_values' => $option->values->map(function ($value) use ($option) {
                return [
                    'id' => $value->id,
                    'key' => Str::random(),
                    'value' => $value->translate('name'),
                    'position' => $value->position,
                    'readonly' => $option->shared,
                ];
            })->toArray(),
        ];
    }
}
