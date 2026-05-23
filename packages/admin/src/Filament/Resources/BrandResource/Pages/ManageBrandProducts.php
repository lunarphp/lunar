<?php

namespace Lunar\Admin\Filament\Resources\BrandResource\Pages;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\BrandResource;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Core\Models\Product;
use Lunar\Filament\Forms\Components\ProductSelect;
use Lunar\Filament\Tables\Product\ProductTable;

class ManageBrandProducts extends BaseManageRelatedRecords
{
    protected static string $resource = BrandResource::class;

    protected static string $relationship = 'products';

    public function getTitle(): string
    {

        return __('lunarpanel::brand.pages.products.label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::products');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::brand.pages.products.label');
    }

    public function table(Table $table): Table
    {
        return parent::table($table);
    }

    protected function getDefaultTable(Table $table): Table
    {
        return $table->columns([
            ProductTable::getNameColumn()->searchable()
                ->url(function (Model $record) {
                    return ProductResource::getUrl('edit', [
                        'record' => $record->getKey(),
                    ]);
                }),
            ProductTable::getSkuColumn(),
        ])->recordActions([
            DetachAction::make()
                ->action(function (Model $record) {
                    $record->update([
                        'brand_id' => null,
                    ]);

                    Notification::make()
                        ->success()
                        ->body(__('lunarpanel::brand.pages.products.actions.detach.notification.success'))
                        ->send();
                }),
        ])->headerActions([
            AttachAction::make()
                ->label(
                    __('lunarpanel::brand.pages.products.actions.attach.label')
                )
                ->form([
                    ProductSelect::make('recordId')
                        ->label(
                            __('lunarpanel::brand.pages.products.actions.attach.form.record_id.label')
                        )
                        ->required(),
                ])
                ->action(function (array $arguments, array $data) {
                    Product::where('id', '=', $data['recordId'])
                        ->update([
                            'brand_id' => $this->getRecord()->id,
                        ]);

                    Notification::make()
                        ->success()
                        ->body(__('lunarpanel::brand.pages.products.actions.attach.notification.success'))
                        ->send();
                }),
        ]);
    }
}
