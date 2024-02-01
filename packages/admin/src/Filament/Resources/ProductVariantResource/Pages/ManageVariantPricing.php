<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Admin\Support\RelationManagers\PriceRelationManager;
use Lunar\Models\Price;

class ManageVariantPricing extends BaseEditRecord
{
    protected static string $resource = ProductVariantResource::class;

    public ?string $tax_class_id = '';

    public ?string $tax_ref = '';

    public array $basePrices = [];

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $variant = $this->getRecord();

        $this->tax_class_id = $variant->tax_class_id;
        $this->tax_ref = $variant->tax_ref;
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-pricing');
    }

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel::relationmanagers.pricing.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::relationmanagers.pricing.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            ProductVariantResource::getVariantSwitcherWidget(
                $this->getRecord()
            ),
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->url(function (Model $record) {
            return ProductResource::getUrl('variants', [
                'record' => $record->product,
            ]);
        });
    }

    public function getBreadcrumbs(): array
    {
        return [
            ...ProductVariantResource::getBaseBreadcrumbs(
                $this->getRecord()
            ),
            ProductVariantResource::getUrl('pricing', [
                'record' => $this->getRecord(),
            ]) => $this->getTitle(),
        ];
    }

    public function form(Form $form): Form
    {
        if (! count($this->basePrices)) {
            $this->basePrices = ProductResource\Pages\ManageProductPricing::getBasePrices(
                $this->getRecord()
            );
        }

        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Group::make([
                    ProductVariantResource::getTaxClassIdFormComponent(),
                    ProductVariantResource::getTaxRefFormComponent(),
                ])->columns(2),
            ]),
            Forms\Components\Section::make(
                __('lunarpanel::relationmanagers.pricing.form.basePrices.title')
            )
                ->schema(
                    collect($this->basePrices)->map(function ($price, $index): Forms\Components\TextInput {
                        return Forms\Components\TextInput::make('value')
                            ->label('')
                            ->statePath($index.'.value')
                            ->label($price['label'])
                            ->hintColor('warning')
                            ->extraInputAttributes([
                                'class' => '',
                            ])
                            ->hintIcon(function (Forms\Get $get, Forms\Components\TextInput $component) use ($index) {
                                if ($get('basePrices.'.$index.'.id', true)) {
                                    return null;
                                }

                                return FilamentIcon::resolve('lunar::info');
                            })->hintIconTooltip(function (Forms\Get $get, Forms\Components\TextInput $component) use ($index) {
                                if ($get('basePrices.'.$index.'.id', true)) {
                                    return null;
                                }

                                return __('lunarpanel::relationmanagers.pricing.form.basePrices.tooltip');
                            })->live();
                    })->toArray()
                )->statePath('basePrices')->columns(3),
        ])->statePath('');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $variant = $this->getRecord();

        $prices = collect($data['basePrices']);
        unset($data['basePrices']);
        $variant->update($data);

        $prices->filter(
            fn ($price) => ! $price['id']
        )->each(fn ($price) => $variant->prices()->create([
            'currency_id' => $price['currency_id'],
            'price' => (int) ($price['value'] * $price['factor']),
            'min_quantity' => 1,
            'customer_group_id' => null,
        ])
        );

        $prices->filter(
            fn ($price) => $price['id']
        )->each(fn ($price) => Price::whereId($price['id'])->update([
            'price' => (int) ($price['value'] * $price['factor']),
        ])
        );

        $this->basePrices = ProductResource\Pages\ManageProductPricing::getBasePrices($variant);

        return $record;
    }

    public function getRelationManagers(): array
    {
        return [
            PriceRelationManager::class,
        ];
    }
}
