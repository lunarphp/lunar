<?php

namespace Lunar\Admin\Filament\Resources\CustomerResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Lunar\Admin\Filament\Resources\CustomerResource;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    public function getHeading(): string
    {
        return "{$this->record->first_name} {$this->record->last_name}";
    }

    public function getSubheading(): ?string
    {
        return $this->record->company_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerResource\Widgets\CustomerStatsOverviewWidget::class,
        ];
    }
}
