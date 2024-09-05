<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Lunar\Admin\Events\ProductPricingUpdated;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;
use Lunar\Facades\DB;
use Lunar\Facades\ModelManifest;
use Lunar\Models\Contracts\Price as PriceContract;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;

class CustomerGroupPricingRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'prices';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::relationmanagers.customer_group_pricing.title');
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('lunarpanel::relationmanagers.customer_group_pricing.table.heading');
    }

    public function getDefaultForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\Select::make('currency_id')
                        ->label(
                            __('lunarpanel::relationmanagers.pricing.form.currency_id.label')
                        )->relationship(name: 'currency', titleAttribute: 'name')
                        ->default(function () {
                            return Currency::modelClass()::getDefault()?->id;
                        })
                        ->helperText(
                            __('lunarpanel::relationmanagers.pricing.form.currency_id.helper_text')
                        )->required(),
                    Forms\Components\Select::make('customer_group_id')
                        ->label(
                            __('lunarpanel::relationmanagers.pricing.form.customer_group_id.label')
                        )->helperText(
                            __('lunarpanel::relationmanagers.pricing.form.customer_group_id.helper_text')
                        )->relationship(name: 'customerGroup', titleAttribute: 'name')
                        ->required()
                        ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, Forms\Get $get) {
                            $owner = $this->getOwnerRecord();

                            return $rule
                                ->when(blank($get('customer_group_id')),
                                    fn (Unique $rule) => $rule->whereNull('customer_group_id'),
                                    fn (Unique $rule) => $rule->where('customer_group_id', $get('customer_group_id')))
                                ->where('min_quantity', 1)
                                ->where('currency_id', $get('currency_id'))
                                ->where('priceable_type', $owner->getMorphClass())
                                ->where('priceable_id', $owner->id);
                        }),
                ])->columns(2),

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
                                ->where('min_quantity', 1)
                                ->where('currency_id', $get('currency_id'))
                                ->where('priceable_type', $owner->getMorphClass())
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

    public function getDefaultTable(Table $table): Table
    {
        $priceClass = ModelManifest::get(PriceContract::class);
        $priceTable = (new $priceClass)->getTable();
        $cgTable = CustomerGroup::modelClass()::query()->select([DB::raw('id as cg_id'), 'name']);

        return $table
            ->recordTitleAttribute('name')
            ->description(
                __('lunarpanel::relationmanagers.customer_group_pricing.table.description')
            )
            ->modifyQueryUsing(
                fn ($query) => $query
                    ->leftJoinSub($cgTable, 'cg', fn ($join) => $join->on('customer_group_id', 'cg.cg_id'))
                    ->where("{$priceTable}.min_quantity", 1)
                    ->whereNotNull("{$priceTable}.customer_group_id")
            )
            ->defaultSort(fn ($query) => $query->orderBy('cg.name')->orderBy('min_quantity'))
            ->emptyStateHeading(
                __('lunarpanel::relationmanagers.customer_group_pricing.table.empty_state.label')
            )
            ->emptyStateDescription(__('lunarpanel::relationmanagers.customer_group_pricing.table.empty_state.description'))
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
                Tables\Columns\TextColumn::make('customerGroup.name')->label(
                    __('lunarpanel::relationmanagers.pricing.table.customer_group.label')
                )->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('currency')
                    ->relationship(name: 'currency', titleAttribute: 'name')
                    ->preload()
                    ->label(
                        __('lunarpanel::relationmanagers.pricing.table.currency.label')
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data) {
                    $currencyModel = Currency::modelClass()::find($data['currency_id']);

                    $data['min_quantity'] = 1;
                    $data['price'] = (int) ($data['price'] * $currencyModel->factor);

                    return $data;
                })->label(
                    __('lunarpanel::relationmanagers.customer_group_pricing.table.actions.create.label')
                )->modalHeading(__('lunarpanel::relationmanagers.customer_group_pricing.table.actions.create.modal.heading'))
                    ->after(
                        fn () => ProductPricingUpdated::dispatch($this->getOwnerRecord())
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->mutateFormDataUsing(function (array $data): array {
                    $currencyModel = Currency::modelClass()::find($data['currency_id']);

                    $data['min_quantity'] = 1;
                    $data['price'] = (int) ($data['price'] * $currencyModel->factor);

                    return $data;
                })->after(
                    fn () => ProductPricingUpdated::dispatch($this->getOwnerRecord())
                ),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
