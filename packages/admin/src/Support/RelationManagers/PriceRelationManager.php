<?php

namespace Lunar\Admin\Support\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
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

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('lunarpanel::relationmanagers.pricing.table.heading');
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
                        ->default(function () {
                            return Currency::getDefault()?->id;
                        })
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
                    Forms\Components\TextInput::make('min_quantity')
                        ->label(
                            __('lunarpanel::relationmanagers.pricing.form.min_quantity.label')
                        )->helperText(
                            __('lunarpanel::relationmanagers.pricing.form.min_quantity.helper_text')
                        )->numeric()
                        ->default(2)
                        ->minValue(2)
                        ->required()
                        ->rules([
                            fn (Forms\Get $get) => function (string $attribute, $value, Closure $fail) use ($get, $form) {
                                $owner = $this->getOwnerRecord();

                                $price = $form->getModel();

                                $exist = $price::query()
                                    ->when(blank($get('customer_group_id')),
                                        fn ($query) => $query->whereNull('customer_group_id'),
                                        fn ($query) => $query->where('customer_group_id', $get('customer_group_id')))
                                    ->where('currency_id', $get('currency_id'))
                                    ->where('priceable_type', get_class($owner))
                                    ->where('priceable_id', $owner->id)
                                    ->where('min_quantity', $get('min_quantity'))
                                    ->count();

                                if ($exist) {
                                    $fail(__('lunarpanel::relationmanagers.pricing.form.min_quantity.validation.unique'));
                                }
                            },
                        ]),
                ])->columns(3),

                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('price')->formatStateUsing(
                        fn ($state) => $state?->decimal(rounding: false)
                    )->numeric()->unique(
                        modifyRuleUsing: function (Unique $rule, Forms\Get $get) {
                            $owner = $this->getOwnerRecord();

                            return $rule
                                ->when(blank($get('customer_group_id')),
                                    fn (Unique $rule) => $rule->whereNull('customer_group_id'),
                                    fn (Unique $rule) => $rule->where('customer_group_id', $get('customer_group_id')))
                                ->where('min_quantity', $get('min_quantity'))
                                ->where('currency_id', $get('currency_id'))
                                ->where('priceable_type', get_class($owner))
                                ->where('priceable_id', $owner->id);
                        }
                    )->helperText(
                        __('lunarpanel::relationmanagers.pricing.form.price.helper_text')
                    )->required(),
                    Forms\Components\TextInput::make('compare_price')->formatStateUsing(
                        fn ($state) => $state?->decimal(rounding: false)
                    )->label(
                        __('lunarpanel::relationmanagers.pricing.form.compare_price.label')
                    )->helperText(
                        __('lunarpanel::relationmanagers.pricing.form.compare_price.helper_text')
                    )->numeric(),
                ])->columns(2),
            ])->columns(1);
    }

    public function table(Table $table): Table
    {
        $priceTable = (new Price)->getTable();

        return $table
            ->recordTitleAttribute('name')
            ->description(
                __('lunarpanel::relationmanagers.pricing.table.description')
            )
            ->modifyQueryUsing(
                fn ($query) => $query
                    ->where("{$priceTable}.min_quantity", '>', 1)
                    ->orderBy("{$priceTable}.min_quantity", 'asc')
            )->emptyStateHeading(
                __('lunarpanel::relationmanagers.pricing.table.empty_state.label')
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
                Tables\Columns\TextColumn::make('min_quantity')->label(
                    __('lunarpanel::relationmanagers.pricing.table.min_quantity.label')
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
                Tables\Filters\SelectFilter::make('min_quantity')->options(
                    Price::where('priceable_id', $this->getOwnerRecord()->id)
                        ->where('priceable_type', get_class($this->getOwnerRecord()))
                        ->get()
                        ->pluck('min_quantity', 'min_quantity')
                )->label(
                    __('lunarpanel::relationmanagers.pricing.table.min_quantity.label')
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
