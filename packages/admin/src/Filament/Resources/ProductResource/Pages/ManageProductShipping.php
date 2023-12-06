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
            ...$this->dimensions,
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
                'volume_value' => $this->volume,
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

    public function getVolumeProperty()
    {
        $dimensions = $this->dimensions;

        $width = Converter::value($dimensions['width_value'])
            ->from('length.'.$dimensions['width_unit'])
            ->to('length.cm')
            ->convert()
            ->getValue();
        $length = Converter::value($dimensions['length_value'])
            ->from('length.'.$dimensions['length_unit'])
            ->to('length.cm')
            ->convert()
            ->getValue();

        $height = Converter::value($dimensions['height_value'])
            ->from('length.'.$dimensions['height_unit'])
            ->to('length.cm')
            ->convert()
            ->getValue();

        return Converter::from('volume.ml')->to('volume.l')->value($length * $width * $height)->convert()->getValue();
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
                    __('lunarpanel::product.pages.shipping.form.shippable.label')
                )->columnSpan(2),

                TextInputSelectAffix::make('dimensions.length_value')
                    ->label(
                        __('lunarpanel::product.pages.shipping.form.length_value.label')
                    )
                    ->numeric()
                    ->select(
                        fn () => Select::make('length_unit')
                            ->options($lengths)
                            ->label(
                                __('lunarpanel::product.pages.shipping.form.length_unit.label')
                            )->selectablePlaceholder(false)
                    ),
                TextInputSelectAffix::make('dimensions.width_value')
                    ->label(
                        __('lunarpanel::product.pages.shipping.form.width_value.label')
                    )
                    ->numeric()
                    ->select(
                        fn () => Select::make('width_unit')
                            ->options($lengths)
                            ->label(
                                __('lunarpanel::product.pages.shipping.form.width_unit.label')
                            )->selectablePlaceholder(false)
                    ),
                TextInputSelectAffix::make('dimensions.height_value')
                    ->label(
                        __('lunarpanel::product.pages.shipping.form.height_value.label')
                    )
                    ->numeric()
                    ->select(
                        fn () => Select::make('height_unit')
                            ->options($lengths)
                            ->label(
                                __('lunarpanel::product.pages.shipping.form.height_unit.label')
                            )->selectablePlaceholder(false)
                    ),
                TextInputSelectAffix::make('dimensions.weight_value')
                    ->label(
                        __('lunarpanel::product.pages.shipping.form.weight_value.label')
                    )
                    ->numeric()
                    ->select(
                        fn () => Select::make('weight_unit')
                            ->options($weights)
                            ->label(
                                __('lunarpanel::product.pages.shipping.form.weight_unit.label')
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
