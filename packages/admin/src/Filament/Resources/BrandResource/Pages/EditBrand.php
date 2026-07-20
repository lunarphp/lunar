<?php

namespace Lunar\Admin\Filament\Resources\BrandResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Lunar\Admin\Filament\Resources\BrandResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Core\Actions\Brands\DeleteBrand;

class EditBrand extends BaseEditRecord
{
    protected static string $resource = BrandResource::class;

    public function getTitle(): string
    {
        return __('lunarpanel::brand.pages.edit.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::product.pages.edit.title');
    }

    protected function getDefaultHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function ($record, DeleteAction $action) {
                    if (DeleteBrand::isProtected($record)) {
                        Notification::make()
                            ->warning()
                            ->body(__('lunarpanel::brand.action.delete.notification.error_protected'))
                            ->send();
                        $action->cancel();
                    }
                }),
        ];
    }
}
