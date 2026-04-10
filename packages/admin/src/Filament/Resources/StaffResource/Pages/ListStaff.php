<?php

namespace Lunar\Admin\Filament\Resources\StaffResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Support\Colors\Color;
use Lunar\Admin\Filament\Resources\StaffResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListStaff extends BaseListRecords
{
    protected static string $resource = StaffResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Action::make('access-control')
                ->label(__('lunarpanel::staff.action.acl.label'))
                ->color(Color::Lime)
                ->url(fn () => StaffResource::getUrl('acl')),
            CreateAction::make(),
        ];
    }
}
