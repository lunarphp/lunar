<?php

namespace Lunar\Admin\Support\Actions\Products;

use Filament\Actions\ForceDeleteAction;

class ForceDeleteProductAction extends ForceDeleteAction
{
    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTransaction();
    }
}
