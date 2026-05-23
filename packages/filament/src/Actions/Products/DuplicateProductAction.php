<?php

namespace Lunar\Filament\Actions\Products;

use Filament\Actions\Action;
use Lunar\Core\Actions\Products\DuplicateProduct;
use Lunar\Core\Models\Product;

class DuplicateProductAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'duplicate';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.products.duplicate.label'))
            ->icon('heroicon-o-document-duplicate')
            ->action(fn (Product $record) => DuplicateProduct::run($record))
            ->successNotificationTitle(__('lunar-filament::actions.products.duplicate.notification.success'));
    }
}
