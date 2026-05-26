<?php

namespace Lunar\Filament\Actions\Products;

use Filament\Actions\Action;
use Lunar\Core\Contracts\Actions\Products\DuplicatesProduct;
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
            ->action(fn (Product $record) => app(DuplicatesProduct::class)->execute($record))
            ->successNotificationTitle(__('lunar-filament::actions.products.duplicate.notification.success'));
    }
}
