<?php

namespace Lunar\Admin\Filament\Resources\BrandResource\Pages;

use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\BrandResource;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Models\Product;

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
        return $table->columns([
            ProductResource::getNameTableColumn()->searchable(),
            ProductResource::getSkuTableColumn(),
        ])->actions([
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
                ->form([
                    Forms\Components\Select::make('recordId')
                        ->label('Product')
                        ->required()
                        ->searchable(true)
                        ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                            return Product::search($search)
                                ->get()
                                ->mapWithKeys(fn (Product $record): array => [$record->getKey() => $record->translateAttribute('name')])
                                ->all();
                        }),
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
