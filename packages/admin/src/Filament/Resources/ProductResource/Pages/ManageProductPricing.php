<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Admin\Support\RelationManagers\PriceRelationManager;

class ManageProductPricing extends BaseEditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Pricing';

    public ?string $tax_class_id = '';

    public ?string $tax_ref = '';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $variant = $this->getOwnerRecord();

        $this->tax_class_id = $variant->tax_class_id;
        $this->tax_ref = $variant->tax_ref;
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

        $variant->update($data);

        return $record;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Group::make([
                        ProductVariantResource::getTaxClassIdFormComponent(),
                        ProductVariantResource::getTaxRefFormComponent(),
                    ])->columns(2),
                ]),
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
                            ->where('quantity_break', $get('quantity_break'))
                            ->where('currency_id', $get('currency_id'))
                            ->where('priceable_type', get_class($owner))
                            ->where('priceable_id', $owner->id);
                    }
                )->required(),
                Forms\Components\TextInput::make('quantity_break')
                    ->label(
                        __('lunarpanel::relationmanagers.pricing.form.quantity_break.label')
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
                fn ($query) => $query->orderBy('quantity_break', 'asc')
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
                Tables\Columns\TextColumn::make('quantity_break')->label(
                    __('lunarpanel::relationmanagers.pricing.table.quantity_break.label')
                ),
                Tables\Columns\TextColumn::make('customerGroup.name')->label(
                    __('lunarpanel::relationmanagers.pricing.table.customer_group.label')
                ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('currency')
                    ->relationship(name: 'currency', titleAttribute: 'name')
                    ->preload(),
                Tables\Filters\SelectFilter::make('quantity_break')->options(
                    Price::where('priceable_id', $this->getOwnerRecord()->id)
                        ->where('priceable_type', get_class($this->getOwnerRecord()))
                        ->get()
                        ->pluck('quantity_break', 'quantity_break')
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
