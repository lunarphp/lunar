<?php

namespace Lunar\Admin\Support\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Lunar\Models\Currency;
use Lunar\Models\Price;

class PriceRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::relationmanagers.pricing.title');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\Select::make('currency_id')
                        ->label(
                            __('lunarpanel::relationmanagers.pricing.form.currency_id.label')
                        )->relationship(name: 'currency', titleAttribute: 'name')
                        ->helperText(
                            __('lunarpanel::relationmanagers.pricing.form.currency_id.helper_text')
                        )->required(),
                    Forms\Components\Select::make('customer_group_id')
                        ->label(
                            __('lunarpanel::relationmanagers.pricing.form.customer_group_id.label')
                        )->placeholder(
                            __('lunarpanel::relationmanagers.pricing.form.customer_group_id.placeholder')
                        )->helperText(
                            __('lunarpanel::relationmanagers.pricing.form.customer_group_id.helper_text')
                        )->relationship(name: 'customerGroup', titleAttribute: 'name'),
                    Forms\Components\TextInput::make('tier')
                        ->label(
                            __('lunarpanel::relationmanagers.pricing.form.tier.label')
                        )->helperText(
                            __('lunarpanel::relationmanagers.pricing.form.tier.helper_text')
                        )->numeric()->minValue(1)->required(),
                ])->columns(3),

                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('price')->formatStateUsing(
                        fn ($state) => $state?->decimal(rounding: false)
                    )->numeric()->unique(
                        modifyRuleUsing: function (Unique $rule, Forms\Get $get) {
                            $owner = $this->getOwnerRecord();

                            return $rule->where('customer_group_id', $get('customer_group_id'))
                                ->where('tier', 1)
                                ->where('currency_id', 1)
                                ->where('priceable_type', get_class($owner))
                                ->where('priceable_id', $owner->id);
                        }
                    )->required(),
                    Forms\Components\TextInput::make('compare_price')->formatStateUsing(
                        fn ($state) => $state?->decimal(rounding: false)
                    )->numeric(),
                ])->columns(2),
            ])->columns(1);
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
                    )->sortable(),
                Tables\Columns\TextColumn::make('currency.code')->label(
                    __('lunarpanel::relationmanagers.pricing.table.currency.label')
                )->sortable(),
                Tables\Columns\TextColumn::make('tier')->label(
                    __('lunarpanel::relationmanagers.pricing.table.tier.label')
                )->sortable(),
                Tables\Columns\TextColumn::make('customerGroup.name')->label(
                    __('lunarpanel::relationmanagers.pricing.table.customer_group.label')
                )->placeholder(
                    __('lunarpanel::relationmanagers.pricing.table.customer_group.placeholder')
                )->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('currency')
                    ->relationship(name: 'currency', titleAttribute: 'name')
                    ->preload()
                    ->label(
                        __('lunarpanel::relationmanagers.pricing.table.currency.label')
                    ),
                Tables\Filters\SelectFilter::make('tier')->options(
                    Price::where('priceable_id', $this->getOwnerRecord()->id)
                        ->where('priceable_type', get_class($this->getOwnerRecord()))
                        ->get()
                        ->pluck('tier', 'tier')
                )->label(
                    __('lunarpanel::relationmanagers.pricing.table.tier.label')
                ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data) {
                    $currencyModel = Currency::find($data['currency_id']);

                    $data['price'] = (int) ($data['price'] * $currencyModel->factor);

                    return $data;
                })->label(
                    __('lunarpanel::relationmanagers.pricing.table.actions.create.label')
                ),
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
