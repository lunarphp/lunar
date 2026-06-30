<?php

namespace Lunar\Admin\Support\RelationManagers;

use Closure;
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
use Lunar\Core\Facades\DB;
use Lunar\Core\Facades\PriceCalculator;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Price;
use Lunar\Filament\Forms\Components\CurrencySelect;
use Lunar\Filament\Forms\Components\CustomerGroupSelect;

class PriceRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'prices';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::relationmanagers.pricing.tab_name');
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('lunarpanel::relationmanagers.pricing.table.heading');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    CurrencySelect::make('currency_id')
                        ->label(
                            __('lunarpanel::relationmanagers.pricing.form.currency_id.label')
                        )
                        ->helperText(
                            __('lunarpanel::relationmanagers.pricing.form.currency_id.helper_text')
                        )
                        ->required(),
                    CustomerGroupSelect::make('customer_group_id')
                        ->label(
                            __('lunarpanel::relationmanagers.pricing.form.customer_group_id.label')
                        )
                        ->placeholder(
                            __('lunarpanel::relationmanagers.pricing.form.customer_group_id.placeholder')
                        )
                        ->helperText(
                            __('lunarpanel::relationmanagers.pricing.form.customer_group_id.helper_text')
                        ),
                    TextInput::make('min_quantity')
                        ->label(
                            __('lunarpanel::relationmanagers.pricing.form.min_quantity.label')
                        )->helperText(
                            __('lunarpanel::relationmanagers.pricing.form.min_quantity.helper_text')
                        )->numeric()
                        ->default(2)
                        ->minValue(2)
                        ->required()
                        ->rules([
                            fn (Get $get, $record) => function (string $attribute, $value, Closure $fail) use ($get, $schema, $record) {
                                $owner = $this->getOwnerRecord();

                                $price = $schema->getModel();

                                $exist = $price::query()
                                    ->when(filled($record), fn ($query) => $query->where('id', '!=', $record->id))
                                    ->when(blank($get('customer_group_id')),
                                        fn ($query) => $query->whereNull('customer_group_id'),
                                        fn ($query) => $query->where('customer_group_id', $get('customer_group_id')))
                                    ->where('currency_id', $get('currency_id'))
                                    ->where('priceable_type', $owner->getMorphClass())
                                    ->where('priceable_id', $owner->id)
                                    ->where('min_quantity', $get('min_quantity'))
                                    ->count();

                                if ($exist) {
                                    $fail(__('lunarpanel::relationmanagers.pricing.form.min_quantity.validation.unique'));
                                }
                            },
                        ]),
                ])->columns(3),

                Group::make([
                    TextInput::make('price')->numeric()->helperText(
                        __('lunarpanel::relationmanagers.pricing.form.price.helper_text')
                    )->required(),
                    TextInput::make('list_price')->label(
                        __('lunarpanel::relationmanagers.pricing.form.list_price.label')
                    )->helperText(
                        __('lunarpanel::relationmanagers.pricing.form.list_price.helper_text')
                    )->numeric(),
                ])->columns(2),
            ])->columns(1);
    }

    public function table(Table $table): Table
    {
        $priceTable = (new Price)->getTable();
        $cgTable = CustomerGroup::query()->select([DB::raw('id as cg_id'), 'name']);

        return $table
            ->recordTitleAttribute('name')
            ->description(
                __('lunarpanel::relationmanagers.pricing.table.description')
            )
            ->modifyQueryUsing(
                fn ($query) => $query
                    ->leftJoinSub($cgTable, 'cg', fn ($join) => $join->on('customer_group_id', 'cg.cg_id'))
                    ->where("{$priceTable}.min_quantity", '>', 1)
            )
            ->defaultSort(fn ($query) => $query->orderBy('cg.name')->orderBy('min_quantity'))
            ->emptyStateHeading(
                __('lunarpanel::relationmanagers.pricing.table.empty_state.label')
            )
            ->columns([
                TextColumn::make('price')
                    ->label(
                        __('lunarpanel::relationmanagers.pricing.table.price.label')
                    )->formatStateUsing(
                        fn ($state, $record) => $record->format('price'),
                    )->sortable(),
                TextColumn::make('currency.code')->label(
                    __('lunarpanel::relationmanagers.pricing.table.currency.label')
                )->sortable(),
                TextColumn::make('min_quantity')->label(
                    __('lunarpanel::relationmanagers.pricing.table.min_quantity.label')
                )->sortable(),
                TextColumn::make('customerGroup.name')->label(
                    __('lunarpanel::relationmanagers.pricing.table.customer_group.label')
                )->placeholder(
                    __('lunarpanel::relationmanagers.pricing.table.customer_group.placeholder')
                )->sortable(),
            ])
            ->filters([
                SelectFilter::make('currency')
                    ->relationship(name: 'currency', titleAttribute: 'name')
                    ->preload()
                    ->label(
                        __('lunarpanel::relationmanagers.pricing.table.currency.label')
                    ),
                SelectFilter::make('min_quantity')->options(
                    Price::where('priceable_id', $this->getOwnerRecord()->id)
                        ->where('priceable_type', $this->getOwnerRecord()->getMorphClass())
                        ->get()
                        ->pluck('min_quantity', 'min_quantity')
                )->label(
                    __('lunarpanel::relationmanagers.pricing.table.min_quantity.label')
                ),
            ])
            ->headerActions([
                CreateAction::make()->mutateDataUsing(function (array $data) {
                    $currencyModel = Currency::find($data['currency_id']);

                    $data['price'] = PriceCalculator::toMinor((float) $data['price'], $currencyModel);

                    return $data;
                })->label(
                    __('lunarpanel::relationmanagers.pricing.table.actions.create.label')
                ),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(fn (array $data): array => $this->unwrapPriceData($data))
                    ->mutateDataUsing(function (array $data): array {
                        $currencyModel = Currency::find($data['currency_id']);

                        $data['price'] = PriceCalculator::toMinor((float) $data['price'], $currencyModel);

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

            $data[$key] = PriceCalculator::toMajor((int) $data[$key], $currency);
        }

        return $data;
    }
}
