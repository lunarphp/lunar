<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductResource\RelationManagers\CustomerGroupPricingRelationManager;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Concerns\Products\ManagesProductPricing;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Admin\Support\RelationManagers\PriceRelationManager;
use Lunar\Models\Currency;
use Lunar\Models\Price;

class ManageProductPricing extends BaseEditRecord
{
    use ManagesProductPricing;

    protected static string $resource = ProductResource::class;

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-pricing');
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return ($parameters['record']->variants_count ?? $parameters['record']->variants()->count()) == 1;
    }

    public function getOwnerRecord(): Model
    {
        return $this->getRecord()->variants()->first();
    }

    public function form(Schema $schema): Schema
    {
        if (! count($this->basePrices)) {
            $this->basePrices = $this->getBasePrices();
        }

        $schema->components([
            Section::make()
                ->schema([
                    Group::make([
                        ProductVariantResource::getTaxClassIdFormComponent(),
                        ProductVariantResource::getTaxRefFormComponent(),
                    ])->columns(2),
                ]),
            $this->getBasePriceFormSection(),
        ])->statePath('');

        $this->callLunarHook('extendForm', $schema);

        return $schema;
    }

    public function getRelationManagers(): array
    {
        return [
            CustomerGroupPricingRelationManager::make([
                'ownerRecord' => $this->getOwnerRecord(),
            ]),
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
                TextColumn::make('price')
                    ->label(
                        __('lunarpanel::relationmanagers.pricing.table.price.label')
                    )->formatStateUsing(
                        fn ($state) => $state->formatted,
                    ),
                TextColumn::make('currency.code')->label(
                    __('lunarpanel::relationmanagers.pricing.table.currency.label')
                ),
                TextColumn::make('min_quantity')->label(
                    __('lunarpanel::relationmanagers.pricing.table.min_quantity.label')
                ),
                TextColumn::make('customerGroup.name')->label(
                    __('lunarpanel::relationmanagers.pricing.table.customer_group.label')
                ),
            ])
            ->filters([
                SelectFilter::make('currency')
                    ->relationship(name: 'currency', titleAttribute: 'name')
                    ->preload(),
                SelectFilter::make('min_quantity')->options(
                    Price::where('priceable_id', $this->getOwnerRecord()->id)
                        ->where('priceable_type', $this->getOwnerRecord()->getMorphClass())
                        ->get()
                        ->pluck('min_quantity', 'min_quantity')
                ),
            ])
            ->headerActions([
                CreateAction::make()->mutateDataUsing(function (array $data) {
                    $currencyModel = Currency::find($data['currency_id']);

                    $data['price'] = (int) ($data['price'] * $currencyModel->factor);

                    return $data;
                }),
            ])
            ->recordActions([
                EditAction::make()->mutateDataUsing(function (array $data): array {
                    $currencyModel = Currency::find($data['currency_id']);

                    $data['price'] = (int) ($data['price'] * $currencyModel->factor);

                    return $data;
                }),
                DeleteAction::make(),
            ]);
    }
}
