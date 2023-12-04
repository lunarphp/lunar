<?php

namespace Lunar\Admin\Filament\Resources\StaffResource\Pages;

use Filament\Actions;
use Filament\Support\Colors\Color;
use Lunar\Admin\Filament\Resources\StaffResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListStaff extends BaseListRecords
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('access-control')
                ->label(__('lunarpanel::staff.action.acl.label'))
                ->color(Color::Lime)
                ->url(fn () => StaffResource::getUrl('acl')),
            Actions\CreateAction::make(),
        ];
    }
}
