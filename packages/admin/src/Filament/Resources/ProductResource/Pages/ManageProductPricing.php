<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Admin\Support\RelationManagers\PriceRelationManager;
use Lunar\Models\Currency;
use Lunar\Models\Price;

class ManageProductPricing extends BaseEditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Pricing';

    public ?string $tax_class_id = '';

    public ?string $tax_ref = '';

    public array $basePrices = [];

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $variant = $this->getOwnerRecord();

        $this->tax_class_id = $variant->tax_class_id;
        $this->tax_ref = $variant->tax_ref;
    }

    protected function mapBasePrices()
    {
        // Get enabled currencies
        $currencies = Currency::whereEnabled(true)->get();

        $prices = collect([]);

        foreach ($this->getOwnerRecord()->basePrices()->get() as $price) {
            $prices->put(
                $price->currency->code,
                [
                    'id' => $price->id,
                    'value' => $price->price->decimal(rounding: false),
                    'factor' => $price->currency->factor,
                    'label' => $price->currency->name,
                    'currency_code' => $price->currency->code,
                    'default_currency' => $price->currency->default,
                    'currency_id' => $price->currency_id,
                ]
            );
        }

        $defaultCurrencyPrice = $prices->first(
            fn ($price) => $price['default_currency']
        );

        foreach ($currencies as $currency) {
            if (! $prices->get($currency->code)) {
                $prices->put($currency->code, [
                    'id' => null,
                    'value' => round(($defaultCurrencyPrice['value'] ?? 0) * $currency->exchange_rate, $currency->decimal_places),
                    'factor' => $currency->factor,
                    'label' => $currency->name,
                    'currency_code' => $currency->code,
                    'default_currency' => $currency->default,
                    'currency_id' => $currency->id,
                ]);
            }
        }

        $this->basePrices = $prices->values()->toArray();
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-pricing');
    }

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel::relationmanagers.pricing.title');
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return $parameters['record']->variants()->count() == 1;
    }

    public function getOwnerRecord(): Model
    {
        return $this->getRecord()->variants()->first();
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $variant = $this->getOwnerRecord();

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

        $this->mapBasePrices();

        return $record;
    }

    public function form(Form $form): Form
    {
        if (! count($this->basePrices)) {
            $this->mapBasePrices();
        }

        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Group::make([
                        ProductVariantResource::getTaxClassIdFormComponent(),
                        ProductVariantResource::getTaxRefFormComponent(),
                    ])->columns(2),
                ]),
            Forms\Components\Section::make('Base Prices')
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

    public function getRelationManagers(): array
    {
        return [
            PriceRelationManager::make([
                'ownerRecord' => $this->getOwnerRecord(),
            ]),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::relationmanagers.pricing.title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(
                fn ($query) => $query->orderBy('min_quantity', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('price')
                    ->label(
                        __('lunarpanel::relationmanagers.pricing.table.price.label')
                    )->formatStateUsing(
                        fn ($state) => $state->formatted,
                    ),
                Tables\Columns\TextColumn::make('currency.code')->label(
                    __('lunarpanel::relationmanagers.pricing.table.currency.label')
                ),
                Tables\Columns\TextColumn::make('min_quantity')->label(
                    __('lunarpanel::relationmanagers.pricing.table.min_quantity.label')
                ),
                Tables\Columns\TextColumn::make('customerGroup.name')->label(
                    __('lunarpanel::relationmanagers.pricing.table.customer_group.label')
                ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('currency')
                    ->relationship(name: 'currency', titleAttribute: 'name')
                    ->preload(),
                Tables\Filters\SelectFilter::make('min_quantity')->options(
                    Price::where('priceable_id', $this->getOwnerRecord()->id)
                        ->where('priceable_type', get_class($this->getOwnerRecord()))
                        ->get()
                        ->pluck('min_quantity', 'min_quantity')
                ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data) {
                    $currencyModel = Currency::find($data['currency_id']);

                    $data['price'] = (int) ($data['price'] * $currencyModel->factor);

                    return $data;
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->mutateFormDataUsing(function (array $data): array {
                    $currencyModel = Currency::find($data['currency_id']);

                    $data['price'] = (int) ($data['price'] * $currencyModel->factor);

                    return $data;
                }),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
