<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Concerns\Products\UpdatesPricing;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Admin\Support\RelationManagers\PriceRelationManager;
use Lunar\Models\Currency;
use Lunar\Models\Price;

class ManageProductPricing extends BaseEditRecord
{
    use UpdatesPricing;

    protected static string $resource = ProductResource::class;

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-pricing');
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return $parameters['record']->variants()->count() == 1;
    }

    public function getOwnerRecord(): Model
    {
        return $this->getRecord()->variants()->first();
    }

    public function form(Form $form): Form
    {
        if (! count($this->basePrices)) {
            $this->basePrices = $this->getBasePrices();
        }

        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Group::make([
                        ProductVariantResource::getTaxClassIdFormComponent(),
                        ProductVariantResource::getTaxRefFormComponent(),
                    ])->columns(2),
                ]),
            $this->getBasePriceFormSection(),
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
