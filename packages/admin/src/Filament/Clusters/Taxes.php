<?php

namespace Lunar\Admin\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Support\Facades\FilamentIcon;

class Taxes extends Cluster
{
    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::taxclass.label') ?? static::$navigationLabel ?? static::$title ?? str(class_basename(static::class))
            ->beforeLast('Cluster')
            ->kebab()
            ->replace('-', ' ')
            ->title();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::tax');
    }
}
