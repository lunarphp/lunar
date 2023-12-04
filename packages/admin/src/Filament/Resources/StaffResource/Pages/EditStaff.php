<?php

namespace Lunar\Admin\Filament\Resources\StaffResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\StaffResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditStaff extends BaseEditRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
