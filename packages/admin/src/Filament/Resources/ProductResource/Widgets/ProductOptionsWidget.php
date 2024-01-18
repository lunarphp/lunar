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
use Lunar\Models\ProductOption;
use Lunar\Utils\Arr;

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

    public bool $configuringOptions = true;

    public function mount()
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
    }

    public function cancelOptionConfiguring(): void
    {
        $this->configuringOptions = false;
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

    public function addOptionValue($path)
    {
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

    public function getVariantPermutationsProperty()
    {
        $permutations = Arr::permutate(
            collect($this->configuredOptions)
                ->filter(
                    fn ($option) => $option['value']
                )
                ->mapWithKeys(
                    fn ($option) => [$option['value'] => collect($option['option_values'])
                        ->map(
                            fn ($value) => $value['value']
                        )]
                )->toArray()
        );

        if (count($this->configuredOptions) == 1) {
            $newPermutations = [];
            foreach ($permutations as $p) {
                $newPermutations[] = [
                    $this->configuredOptions[0]['value'] => $p,
                ];
            }
            $permutations = $newPermutations;
        }

        $variants = $this->record->variants->load('values.option')->map(function ($variant) {
            return [
                'model' => $variant,
                'values' => $variant->values->mapWithKeys(
                    fn ($value) => [$value->option->translate('name') => $value->translate('name')]
                )->toArray(),
            ];
        });

        $variantPermutations = [];

        foreach ($permutations as $permutation) {
            $variantIndex = $variants->search(function ($variant) use ($permutation) {
                $diffCount = count(array_diff($permutation, $variant['values']));
                $amountMatched = count($permutation) - $diffCount;

                return ! $diffCount || $amountMatched == count($variant['values']);
            });

            $variant = $variants[$variantIndex]['model'] ?? null;

            $variantPermutations[] = [
                'variant_id' => $variant?->id,
                'sku' => $variant?->sku,
                'values' => $permutation,
            ];

            if (! is_null($variantIndex)) {
                $variants->forget($variantIndex);
            }
        }

        return $variantPermutations;
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
