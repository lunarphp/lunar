<?php

namespace Lunar\Admin\Filament\Pages;

use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Support\Pages\BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::dashboard');
    }
}
