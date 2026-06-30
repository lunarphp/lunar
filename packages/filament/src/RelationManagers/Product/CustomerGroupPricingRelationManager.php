<?php

namespace Lunar\Filament\RelationManagers\Product;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Price;
use Lunar\Filament\Forms\Components\CurrencySelect;
use Lunar\Filament\Forms\Components\CustomerGroupSelect;
use Lunar\Filament\RelationManagers\BaseRelationManager;

class CustomerGroupPricingRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'prices';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::relationmanagers.customer_group_pricing.title');
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('lunar-filament::relationmanagers.customer_group_pricing.table.heading');
    }

    public function getDefaultForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    CurrencySelect::make('currency_id')
                        ->label(
                            __('lunar-filament::relationmanagers.pricing.form.currency_id.label')
                        )
                        ->helperText(
                            __('lunar-filament::relationmanagers.pricing.form.currency_id.helper_text')
                        )
                        ->required(),
                    CustomerGroupSelect::make('customer_group_id')
                        ->label(
                            __('lunar-filament::relationmanagers.pricing.form.customer_group_id.label')
                        )
                        ->helperText(
                            __('lunar-filament::relationmanagers.pricing.form.customer_group_id.helper_text')
                        )
                        ->required()
                        ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, Get $get) {
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

                Group::make([
                    TextInput::make('price')->numeric()->helperText(
                        __('lunar-filament::relationmanagers.pricing.form.price.helper_text')
                    )->required(),
                    TextInput::make('list_price')->label(
                        __('lunar-filament::relationmanagers.pricing.form.list_price.label')
                    )->helperText(
                        __('lunar-filament::relationmanagers.pricing.form.list_price.helper_text')
                    )->numeric(),
                ])->columns(2),
            ])->columns(1);
    }

    public function getDefaultTable(Table $table): Table
    {
        $priceTable = (new Price)->getTable();
        $cgTable = CustomerGroup::query()->select([DB::raw('id as cg_id'), 'name']);

        return $table
            ->recordTitleAttribute('name')
            ->description(
                __('lunar-filament::relationmanagers.customer_group_pricing.table.description')
            )
            ->modifyQueryUsing(
                fn ($query) => $query
                    ->leftJoinSub($cgTable, 'cg', fn ($join) => $join->on('customer_group_id', 'cg.cg_id'))
                    ->where("{$priceTable}.min_quantity", 1)
                    ->whereNotNull("{$priceTable}.customer_group_id")
            )
            ->defaultSort(fn ($query) => $query->orderBy('cg.name')->orderBy('min_quantity'))
            ->emptyStateHeading(
                __('lunar-filament::relationmanagers.customer_group_pricing.table.empty_state.label')
            )
            ->emptyStateDescription(__('lunar-filament::relationmanagers.customer_group_pricing.table.empty_state.description'))
            ->columns([
                TextColumn::make('price')
                    ->label(
                        __('lunar-filament::relationmanagers.pricing.table.price.label')
                    )->formatStateUsing(
                        fn ($state, $record) => $record->format('price'),
                    )->sortable(),
                TextColumn::make('currency.code')->label(
                    __('lunar-filament::relationmanagers.pricing.table.currency.label')
                )->sortable(),
                TextColumn::make('customerGroup.name')->label(
                    __('lunar-filament::relationmanagers.pricing.table.customer_group.label')
                )->sortable(),
            ])
            ->filters([
                SelectFilter::make('currency')
                    ->relationship(name: 'currency', titleAttribute: 'name')
                    ->preload()
                    ->label(
                        __('lunar-filament::relationmanagers.pricing.table.currency.label')
                    ),
            ])
            ->headerActions([
                CreateAction::make()->mutateDataUsing(function (array $data) {
                    $currencyModel = Currency::find($data['currency_id']);

                    $data['min_quantity'] = 1;
                    $data['price'] = (int) ($data['price'] * $currencyModel->factor);
                    $data['list_price'] = (int) ($data['list_price'] * $currencyModel->factor);

                    return $data;
                })->label(
                    __('lunar-filament::relationmanagers.customer_group_pricing.table.actions.create.label')
                )->modalHeading(__('lunar-filament::relationmanagers.customer_group_pricing.table.actions.create.modal.heading')),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(fn (array $data): array => $this->unwrapPriceData($data))
                    ->mutateDataUsing(function (array $data): array {
                        $currencyModel = Currency::find($data['currency_id']);

                        $data['min_quantity'] = 1;
                        $data['price'] = (int) ($data['price'] * $currencyModel->factor);
                        $data['list_price'] = (int) ($data['list_price'] * $currencyModel->factor);

                        return $data;
                    }),
                DeleteAction::make(),
            ]);
    }

    protected function unwrapPriceData(array $data): array
    {
        $currencyId = $data['currency_id'] ?? null;
        $currency = $currencyId ? Currency::find($currencyId) : null;

        foreach (['price', 'list_price'] as $key) {
            if (! isset($data[$key]) || $data[$key] === null || ! $currency) {
                continue;
            }

            $data[$key] = ((int) $data[$key]) / $currency->factor;
        }

        return $data;
    }
}
