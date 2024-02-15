<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Cartalyst\Converter\Laravel\Facades\Converter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\ManageVariantShipping;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Models\ProductVariant;
use Marvinosswald\FilamentInputSelectAffix\TextInputSelectAffix;

class ManageProductShipping extends BaseEditRecord
{
    protected static string $resource = ProductResource::class;

    public bool $shippable = true;

    public ?array $dimensions = [
        'length_value' => 0,
        'length_unit' => 'mm',
        'width_value' => 0,
        'width_unit' => 'mm',
        'height_value' => 0,
        'height_unit' => 'mm',
        'weight_value' => 0,
        'weight_unit' => 'kg',
    ];

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel::product.pages.shipping.label');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::product.pages.shipping.label');
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return $parameters['record']->variants()->count() == 1;
    }

    public function getBreadcrumb(): string
    {
        return __('lunarpanel::product.pages.shipping.label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-shipping');
    }

    protected function getDefaultHeaderActions(): array
    {
        return [];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $variant = $this->getVariant();

        $this->dimensions = [
            ...$variant->only(array_keys($this->dimensions)),
        ];
        $this->shippable = $variant->shippable;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $variant = $this->getVariant();

        $variant->update([
            ...[
                'shippable' => $this->shippable,
                'volume_unit' => 'l',
                'volume_value' => ManageVariantShipping::getVolume(
                    [
                        'value' => $this->dimensions['width_value'],
                        'unit' => $this->dimensions['width_unit'],
                    ],
                    [
                        'value' => $this->dimensions['length_value'],
                        'unit' => $this->dimensions['length_unit'],
                    ],
                    [
                        'value' => $this->dimensions['height_value'],
                        'unit' => $this->dimensions['height_unit'],
                    ]
                ),
            ],
            ...$this->dimensions,
        ]);

        return $record;
    }

    protected function getVariant(): ProductVariant
    {
        return $this->getRecord()->variants()->first();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }

    public function form(Form $form): Form
    {
        $measurements = Converter::getMeasurements();

        $lengths = collect(
            array_keys($measurements['length'] ?? [])
        )->mapWithKeys(
            fn ($value) => [$value => $value]
        );

        $weights = collect(
            array_keys($measurements['weight'] ?? [])
        )->mapWithKeys(
            fn ($value) => [$value => $value]
        );

        return $form->schema([
            Section::make()->schema([
                Toggle::make('shippable')->label(
                    __('lunarpanel::productvariant.form.shippable.label')
                )->columnSpan(2),

                TextInputSelectAffix::make('dimensions.length_value')
                    ->label(
                        __('lunarpanel::productvariant.form.length_value.label')
                    )
                    ->numeric()
                    ->select(
                        fn () => Select::make('length_unit')
                            ->options($lengths)
                            ->label(
                                __('lunarpanel::pproductvariant.form.length_unit.label')
                            )->selectablePlaceholder(false)
                    ),
                TextInputSelectAffix::make('dimensions.width_value')
                    ->label(
                        __('lunarpanel::productvariant.form.width_value.label')
                    )
                    ->numeric()
                    ->select(
                        fn () => Select::make('width_unit')
                            ->options($lengths)
                            ->label(
                                __('lunarpanel::productvariant.form.width_unit.label')
                            )->selectablePlaceholder(false)
                    ),
                TextInputSelectAffix::make('dimensions.height_value')
                    ->label(
                        __('lunarpanel::productvariant.form.height_value.label')
                    )
                    ->numeric()
                    ->select(
                        fn () => Select::make('height_unit')
                            ->options($lengths)
                            ->label(
                                __('lunarpanel::productvariant.form.height_unit.label')
                            )->selectablePlaceholder(false)
                    ),
                TextInputSelectAffix::make('dimensions.weight_value')
                    ->label(
                        __('lunarpanel::productvariant.form.weight_value.label')
                    )
                    ->numeric()
                    ->select(
                        fn () => Select::make('weight_unit')
                            ->options($weights)
                            ->label(
                                __('lunarpanel::productvariant.form.weight_unit.label')
                            )->selectablePlaceholder(false)
                    ),
            ])->columns([
                'sm' => 1,
                'xl' => 2,
            ]),
        ])->statePath('');
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
