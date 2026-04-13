<?php

namespace Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource;

class ManageShippingExclusions extends ManageRelatedRecords
{
    protected static string $resource = ShippingZoneResource::class;

    protected static string $relationship = 'shippingExclusions';

    protected static ?string $recordTitle = 'name';

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel.shipping::relationmanagers.exclusions.title_plural');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::shipping-exclusion-lists');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel.shipping::relationmanagers.exclusions.title_plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table->columns(
            ShippingExclusionListResource::getTableColumns(),
        )->headerActions([
            AttachAction::make()
                ->color('primary')
                ->label(
                    __('lunarpanel.shipping::relationmanagers.exclusions.actions.attach.label')
                )
                ->preloadRecordSelect()
                ->recordTitleAttribute('name'),
        ])->actions([
            DetachAction::make('detach')
                ->label(
                    __('lunarpanel.shipping::relationmanagers.exclusions.actions.detach.label')
                ),

        ]);
    }
}
