<?php

namespace Lunar\Filament\Widgets\Products;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Callout;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Core\Models\ProductVariant;
use Lunar\Filament\Actions\Products\MapVariantsToProductOptions;
use Lunar\Filament\Support\RecordUrls;

class ProductOptionsWidget extends BaseWidget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'lunar-filament::widgets.products.product-options';

    public ?Model $record;

    public array $variants = [];

    /**
     * The product options which are being actively configured.
     */
    public array $configuredOptions = [];

    public bool $configuringOptions = false;

    protected static bool $isLazy = false;

    public function mount()
    {
        $this->configureBaseOptions();
    }

    public function addSharedOptionAction(): Action
    {
        $existing = collect($this->configuredOptions)->pluck('id');
        $options = ProductOption::whereNotIn('id', $existing)
            ->shared()
            ->get();

        return Action::make('addSharedOption')
            ->schema([
                Callout::make()
                    ->status('info')
                    ->heading(
                        __('lunar-filament::productoption.widgets.product-options.actions.add-shared-option.form.no_shared_components.label')
                    )
                    ->visible(
                        $options->isEmpty()
                    ),
                Select::make('product_option')
                    ->options(
                        fn () => $options->mapWithKeys(
                            fn ($option) => [$option->id => $option->translate('name')]
                        )
                    )->label(
                        __('lunar-filament::productoption.widgets.product-options.actions.add-shared-option.form.product_option.label')
                    )->visible(
                        $options->isNotEmpty()
                    ),
                Toggle::make('preselect')
                    ->default(true)
                    ->label(
                        __('lunar-filament::productoption.widgets.product-options.actions.add-shared-option.form.preselect.label')
                    )->visible(
                        $options->isNotEmpty()
                    ),
            ])->action(function (array $data) {
                $productOption = ProductOption::with(['values'])->find($data['product_option']);
                $this->configuredOptions[] = $this->mapOption(
                    $productOption,
                    $productOption->values->map(
                        fn ($value) => $this->mapOptionValue($value, $data['preselect'] ?? false)
                    )->toArray()
                );
            });
    }

    public function configureBaseOptions(): void
    {
        $productOptions = $this->query()->get();

        $sharedOptionIds = $productOptions->filter(
            fn ($option) => $option->shared
        )->pluck('id');

        $disabledSharedOptionValues = ProductOptionValue::whereIn(
            'product_option_id',
            $sharedOptionIds
        )->whereNotIn(
            'id',
            $productOptions->pluck('values')->flatten()->pluck('id')
        )->get();

        $options = [];

        foreach ($productOptions as $productOption) {
            $values = $productOption->values->count() ? $productOption->values->map(function ($value) {
                return $this->mapOptionValue($value, true);
            })->merge(
                $disabledSharedOptionValues->filter(
                    fn ($value) => $value->product_option_id == $productOption->id
                )->map(
                    fn ($value) => $this->mapOptionValue($value, false)
                )
            )->sortBy('position')->values()->toArray() : [];

            $options[] = $this->mapOption($productOption, $values);
        }

        $this->configuredOptions = $options;

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
            'value' => '',
            'position' => count($this->configuredOptions) + 1,
            'readonly' => false,
            'option_values' => [
                [
                    'id' => null,
                    'value' => '',
                    'position' => 1,
                    'enabled' => true,
                ],
            ],
        ];
    }

    public function updateConfiguredOptions()
    {
        $this->validate([
            'configuredOptions' => 'array',
            'configuredOptions.*.value' => 'required|string',
            'configuredOptions.*.option_values.*.value' => 'required|string',
        ]);

        $options = collect();

        foreach ($this->configuredOptions as $configuredOption) {
            $enabledCount = collect($configuredOption['option_values'])
                ->filter(
                    fn ($value) => $value['enabled']
                )->count();

            if ($enabledCount) {
                $options->push($configuredOption);
            }
        }

        $this->configuredOptions = $options->values()->toArray();

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
            'value' => '',
            'position' => count($this->configuredOptions[$path]['option_values']) + 1,
            'readonly' => false,
            'enabled' => true,
        ];
    }

    public function removeOptionValue($index, $valueIndex)
    {
        unset($this->configuredOptions[$index]['option_values'][$valueIndex]);
    }

    public function removeOption($index)
    {
        $options = collect($this->configuredOptions)->forget($index);
        $this->configuredOptions = $options->values()->toArray();
    }

    public function updateValuePositions($optionKey, $rows)
    {
        $this->configuredOptions[$optionKey]['option_values'] = $rows;
    }

    public function updateOptionPositions($rows)
    {
        $this->configuredOptions = $rows;
    }

    public function mapVariantPermutations($fillMissing = true): void
    {
        $optionValues = collect($this->configuredOptions)
            ->filter(
                fn ($option) => $option['value']
            )
            ->mapWithKeys(
                fn ($option) => [$option['value'] => collect($option['option_values'])
                    ->filter(
                        fn ($value) => $value['enabled']
                    )
                    ->map(
                        fn ($value) => $value['value']
                    )]
            )->toArray();

        $this->record->loadMissing([
            'variants.basePrices.currency',
            'variants.basePrices.priceable',
            'variants.values.option',
        ]);

        $variants = $this->record->variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => $variant->basePrices->first()?->decimal('price') ?: 0,
                'stock' => $variant->stock_on_hand,
                'values' => $variant->values->mapWithKeys(
                    fn ($value) => [$value->option->translate('name') => $value->translate('name')]
                )->toArray(),
            ];
        })->toArray();

        $this->variants = MapVariantsToProductOptions::map($optionValues, $variants, $fillMissing);
    }

    public function getHasNewVariantsProperty()
    {
        return collect($this->variants)
            ->reject(
                fn ($variant) => $variant['variant_id']
            )->isNotEmpty();
    }

    protected function storeConfiguredOptions(): void
    {
        $language = Language::getDefault();

        foreach ($this->configuredOptions as $optionIndex => $option) {

            $optionModel = empty($option['id']) ?
                new ProductOption([
                    'shared' => false,
                ]) :
                ProductOption::find($option['id']);

            $optionValue = $option['value'];

            if (! $optionModel->shared) {
                $optionModel->name = [
                    $language->code => $optionValue,
                ];
                $optionModel->label = [
                    $language->code => $optionValue,
                ];
                $optionModel->handle = Str::slug($optionValue);
                $optionModel->save();
            }

            if ($optionModel->shared) {
                continue;
            }

            $this->configuredOptions[$optionIndex]['id'] = $optionModel->id;
            $option['id'] = $optionModel->id;

            foreach ($option['option_values'] as $optionValueIndex => $value) {
                $optionValueModel = empty($value['id']) ?
                    new ProductOptionValue([
                        'product_option_id' => $option['id'],
                    ]) :
                    ProductOptionValue::find($value['id']);

                $optionValueModel->name = [
                    $language->code => $value['value'],
                ];
                $optionValueModel->position = $value['position'];
                $optionValueModel->save();

                $this->configuredOptions[$optionIndex]['option_values'][$optionValueIndex]['id'] =
                    $optionValueModel->id;
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

    public function saveVariantsAction(): Action
    {
        return Action::make('saveVariants')
            ->action(function () {
                try {
                    $this->saveVariants();
                } catch (ProductActionException $exception) {
                    DB::rollBack();

                    Notification::make()
                        ->warning()
                        ->body($exception->getMessage())
                        ->send();
                }
            });
    }

    /**
     * Persist the configured options and variant table. Variant removals run
     * through the model's order-history guard — the caller surfaces the
     * domain exception as a notification.
     */
    protected function saveVariants(): void
    {
        DB::beginTransaction();

        $this->storeConfiguredOptions();

        if (! count($this->variants)) {
            $variant = $this->record->variants()->first();
            $variant->values()->detach();
            $this->record->productOptions()->exclusive()->each(
                fn (ProductOption $productOption) => $productOption->delete()
            );

            $this->record->productOptions()->shared()->detach();
            $this->record->variants()
                ->where('id', '!=', $variant->id)
                ->get()
                ->each(
                    fn (ProductVariant $variant) => $variant->delete()
                );

            DB::commit();

            Notification::make()->title(
                __('lunar-filament::productoption.widgets.product-options.notifications.save-variants.success.title')
            )->success()->send();

            return;
        }

        foreach ($this->variants as $variantIndex => $variantData) {
            $variant = new ProductVariant([
                'product_id' => $this->record->id,
            ]);
            $basePrice = null;

            if (! empty($variantData['variant_id'])) {
                $variant = ProductVariant::find($variantData['variant_id']);
                $basePrice = $variant->basePrices->first();
            }

            // A permutation the mapper could not match has no variant to update and
            // nothing to copy tax_class_id or a base price from, so fall back to
            // the oldest sibling.
            if (empty($variantData['variant_id']) && empty($variantData['copied_id'])) {
                $variantData['copied_id'] = $this->record->variants()->orderBy('id')->value('id');
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
            $variant->save();

            // Stock is ledger-derived now — reconcile on_hand at the default
            // location with a movement rather than writing the column.
            $stockDelta = (int) ($variantData['stock'] ?? 0) - $variant->stock_on_hand;

            if ($stockDelta !== 0) {
                $variant->adjustStock(Location::getDefault(), $stockDelta, StockMovementType::Adjustment);
            }

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
            __('lunar-filament::productoption.widgets.product-options.notifications.save-variants.success.title')
        )->success()->send();
    }

    public function getVariantLink($variantId)
    {
        return RecordUrls::for('product_variant', $variantId, ['product' => $this->record]);
    }

    protected function mapOptionValue(ProductOptionValue $value, bool $enabled = true)
    {
        /** @var ProductOptionValue $value */
        return [
            'id' => $value->id,
            'enabled' => $enabled,
            'value' => $value->translate('name'),
            'position' => $value->position,
        ];
    }

    protected function mapOption(ProductOption $option, array $values = []): array
    {
        /** @var ProductOption $option */
        return [
            'id' => $option->id,
            'key' => "option_{$option->id}",
            'value' => $option->translate('name'),
            'position' => $option->pivot?->position ?: count($this->configuredOptions) + 1,
            'readonly' => $option->shared,
            'option_values' => $values,
        ];
    }
}
