<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Widgets;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lunar\Admin\Support\Actions\Products\MapVariantsToProductOptions;
use Lunar\Models\ProductOption;

class ProductOptionsWidget extends BaseWidget implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

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

    public function configureBaseOptions(): void
    {
        $this->configuredOptions = $this->query()->get()->map(function ($option) {
            return [
                'key' => Str::random(),
                'value' => $option->translate('name'),
                'position' => $option->pivot->position,
                'readonly' => $option->shared,
                'option_values' => $option->values->map(function ($value) use ($option) {
                    return [
                        'key' => Str::random(),
                        'value' => $value->translate('name'),
                        'position' => $value->position,
                        'readonly' => $option->shared,
                    ];
                }),
            ];
        })->toArray();

        $this->updateConfiguredOptions();
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
            'key' => Str::random(),
            'value' => '',
            'position' => count($this->configuredOptions) + 1,
            'readonly' => false,
            'option_values' => [
                [
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
            'position' => 1,
            'readonly' => false,
        ];
    }

    public function removeOptionValue($index, $valueIndex)
    {
        if (! $index) {
            unset($this->configuredOptions[$valueIndex]);

        } else {
            unset($this->configuredOptions[$index]['option_values'][$valueIndex]);
        }
    }

    public function mapVariantPermutations(): void
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
                'values' => $variant->values->mapWithKeys(
                    fn ($value) => [$value->option->translate('name') => $value->translate('name')]
                )->toArray(),
            ];
        })->toArray();

        $this->variants = MapVariantsToProductOptions::map($optionValues, $variants);
    }

    public function table(Table $table)
    {
        return $table->query(
            fn () => $this->query()
        )->heading('Product Options')
            ->headerActions([
                Action::make('configure')->label('Configure Options')
                    ->action(
                        fn () => $this->configuringOptions = true
                    ),
            ])
            ->columns([
                TextColumn::make('name')
                    ->formatStateUsing(
                        fn (ProductOption $productOption) => $productOption->translate('name')
                    ),
                TextColumn::make('values')->formatStateUsing(
                    fn (ProductOption $productOption) => $productOption->values->map(
                        fn ($value) => $value->translate('name')
                    )->join(', ')
                ),
            ])->paginated(false);
    }
}
