<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Lunar\Admin\Filament\Resources\DiscountResource\Pages;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\BrandLimitationRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\CollectionLimitationRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\ProductLimitationRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\ProductVariantLimitationRelationManager;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Discount;

class DiscountResource extends BaseResource
{
    protected static ?string $permission = 'sales:manage-discounts';

    protected static ?string $model = Discount::class;

    protected static ?int $navigationSort = 3;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel::discount.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::discount.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::discounts');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sales';
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('')->schema(
                static::getMainFormComponents()
            ),
        ]);
    }

    protected static function getMainFormComponents(): array
    {
        return [
            Forms\Components\Group::make([
                static::getNameFormComponent(),
                static::getHandleFormComponent(),
            ])->columns(2),
            Forms\Components\Group::make([
                static::getStartsAtFormComponent(),
                static::getEndsAtFormComponent(),
            ])->columns(2),
            Forms\Components\Group::make([
                static::getPriorityFormComponent(),
                static::getStopFormComponent(),
            ])->columns(2),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel::discount.form.name.label'))
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                if ($operation !== 'create') {
                    return;
                }
                $set('handle', Str::slug($state));
            })
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getHandleFormComponent(): Component
    {
        return Forms\Components\TextInput::make('handle')
            ->label(__('lunarpanel::discount.form.handle.label'))
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getStartsAtFormComponent(): Component
    {
        return Forms\Components\DateTimePicker::make('starts_at')
            ->label(__('lunarpanel::discount.form.starts_at.label'))
            ->before(function (Forms\Get $get) {
                return $get('ends_at');
            });
    }

    protected static function getEndsAtFormComponent(): Component
    {
        return Forms\Components\DateTimePicker::make('ends_at')
            ->label(__('lunarpanel::discount.form.ends_at.label'));
    }

    protected static function getPriorityFormComponent(): Component
    {
        return Forms\Components\Select::make('priority')
            ->label(__('lunarpanel::discount.form.priority.label'))
            ->helperText(
                __('lunarpanel::discount.form.priority.helper_text')
            )
            ->options(function () {
                return [
                    1 => __('lunarpanel::discount.form.priority.options.low.label'),
                    5 => __('lunarpanel::discount.form.priority.options.medium.label'),
                    10 => __('lunarpanel::discount.form.priority.options.high.label'),
                ];
            });
    }

    protected static function getStopFormComponent(): Component
    {
        return Forms\Components\Toggle::make('stop')
            ->label(
                __('lunarpanel::discount.form.stop.label')
            );
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->searchable();
    }

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('status')
                ->formatStateUsing(function ($state) {
                    return __("lunarpanel::discount.table.status.{$state}.label");
                })
                ->label(__('lunarpanel::discount.table.status.label'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    Discount::ACTIVE => 'success',
                    Discount::EXPIRED => 'danger',
                    Discount::PENDING => 'gray',
                    Discount::SCHEDULED => 'info',
                }),
            Tables\Columns\TextColumn::make('name')
                ->label(__('lunarpanel::discount.table.name.label')),
            Tables\Columns\TextColumn::make('type')
                ->formatStateUsing(function ($state) {
                    return (new $state)->getName();
                })
                ->label(__('lunarpanel::discount.table.type.label')),
            Tables\Columns\TextColumn::make('starts_at')
                ->label(__('lunarpanel::discount.table.starts_at.label'))
                ->date(),
            Tables\Columns\TextColumn::make('ends_at')
                ->label(__('lunarpanel::discount.table.ends_at.label'))
                ->date(),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\EditDiscount::class,
            Pages\ManageDiscountLimitations::class,
        ]);
    }

    protected static function getDefaultRelations(): array
    {
        return [
            CollectionLimitationRelationManager::class,
            BrandLimitationRelationManager::class,
            ProductLimitationRelationManager::class,
            ProductVariantLimitationRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscounts::route('/'),
            'edit' => Pages\EditDiscount::route('/{record}'),
            'limitations' => Pages\ManageDiscountLimitations::route('/{record}/limitations'),
        ];
    }
}
