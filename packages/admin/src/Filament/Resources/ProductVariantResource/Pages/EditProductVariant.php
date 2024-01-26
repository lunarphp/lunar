<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Illuminate\Contracts\Support\Htmlable;
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
        return [];
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
