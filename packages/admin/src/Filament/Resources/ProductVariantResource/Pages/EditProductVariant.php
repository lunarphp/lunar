<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Filament\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditProductVariant extends BaseEditRecord
{
    protected static string $resource = ProductVariantResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel::productvariant.pages.edit.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::productvariant.pages.edit.title');
    }

    public static bool $formActionsAreSticky = true;

    public function getBreadcrumbs(): array
    {

        return ProductVariantResource::getBaseBreadcrumbs(
            $this->getRecord()
        );
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->url(function (Model $record) {
            return ProductResource::getUrl('variants', [
                'record' => $record->product,
            ]);
        });
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $variant = $this->getRecord();

        if ($variant->mappedAttributes->isEmpty()) {
            redirect()->to(
                ProductVariantResource::getUrl('identifiers', [
                    'record' => $this->getRecord(),
                ])
            );
        }
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return $parameters['record']->mappedAttributes->isNotEmpty();
    }

    protected function getDefaultHeaderActions(): array
    {
        return [
            ProductVariantResource::getVariantSwitcherWidget(
                $this->getRecord()
            ),
        ];
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
