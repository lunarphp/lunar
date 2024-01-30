<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Admin\Support\RelationManagers\PriceRelationManager;

class ManageVariantPricing extends BaseEditRecord
{
    protected static string $resource = ProductVariantResource::class;

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-pricing');
    }

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel::relationmanagers.pricing.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::relationmanagers.pricing.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            ProductVariantResource::getVariantSwitcherWidget(
                $this->getRecord()
            ),
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->url(function (Model $record) {
            return ProductResource::getUrl('variants', [
                'record' => $record->product,
            ]);
        });
    }

    public function getBreadcrumbs(): array
    {
        return [
            ...ProductVariantResource::getBaseBreadcrumbs(
                $this->getRecord()
            ),
            ProductVariantResource::getUrl('pricing', [
                'record' => $this->getRecord(),
            ]) => $this->getTitle(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Group::make([
                    ProductVariantResource::getTaxClassIdFormComponent(),
                    ProductVariantResource::getTaxRefFormComponent(),
                ])->columns(2),
            ]),
        ]);
    }

    public function getRelationManagers(): array
    {
        return [
            PriceRelationManager::class,
        ];
    }
}
