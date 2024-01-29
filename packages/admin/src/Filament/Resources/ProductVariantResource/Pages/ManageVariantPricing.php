<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Models\Currency;
use Lunar\Models\Price;

class ManageVariantPricing extends ManageRelatedRecords
{
    protected static string $resource = ProductVariantResource::class;

    protected static string $relationship = 'prices';

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
        return $form
            ->schema([
                Forms\Components\TextInput::make('price')->formatStateUsing(
                    fn ($state) => $state?->decimal(rounding: false)
                )->numeric()->unique(
                    modifyRuleUsing: function (Unique $rule, Forms\Get $get) {
                        $owner = $this->getOwnerRecord();

                        return $rule->where('customer_group_id', $get('customer_group_id'))
                            ->where('tier', $get('tier'))
                            ->where('currency_id', $get('currency_id'))
                            ->where('priceable_type', get_class($owner))
                            ->where('priceable_id', $owner->id);
                    }
                )->required(),
                Forms\Components\TextInput::make('tier')
                    ->label(
                        __('lunarpanel::relationmanagers.pricing.form.tier.label')
                    )->numeric()->minValue(1)->required(),
                Forms\Components\Select::make('currency_id')
                    ->label(
                        __('lunarpanel::relationmanagers.pricing.form.currency_id.label')
                    )->relationship(name: 'currency', titleAttribute: 'name')->required(),
                Forms\Components\Select::make('customer_group_id')
                    ->label(
                        __('lunarpanel::relationmanagers.pricing.form.customer_group_id.label')
                    )->relationship(name: 'customerGroup', titleAttribute: 'name'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(
                fn ($query) => $query->orderBy('tier', 'asc')
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
                Tables\Columns\TextColumn::make('tier')->label(
                    __('lunarpanel::relationmanagers.pricing.table.tier.label')
                ),
                Tables\Columns\TextColumn::make('customerGroup.name')->label(
                    __('lunarpanel::relationmanagers.pricing.table.customer_group.label')
                ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('currency')
                    ->relationship(name: 'currency', titleAttribute: 'name')
                    ->preload(),
                Tables\Filters\SelectFilter::make('tier')->options(
                    Price::where('priceable_id', $this->getOwnerRecord()->id)
                        ->where('priceable_type', get_class($this->getOwnerRecord()))
                        ->get()
                        ->pluck('tier', 'tier')
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
