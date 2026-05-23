<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Lunar\Filament\Support\Concerns\CallsHooks;

abstract class BaseViewRecord extends ViewRecord
{
    use CallsHooks;

    protected function getDefaultFooterWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return $this->callLunarHook('footerWidgets', $this->getDefaultFooterWidgets());
    }

    protected function getDefaultHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return $this->callLunarHook('headerActions', $this->getDefaultHeaderActions());
    }

    protected function getDefaultHeaderWidgets(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return $this->callLunarHook('headerWidgets', $this->getDefaultHeaderWidgets());
    }

    public function getDefaultHeading(): string
    {
        return $this->heading ?? $this->getTitle();
    }

    public function getHeading(): string|Htmlable
    {
        return $this->callLunarHook('heading', $this->getDefaultHeading(), $this->record ?? null);
    }

    public function getDefaultSubheading(): ?string
    {
        return $this->subheading;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return $this->callLunarHook('subHeading', $this->getDefaultSubheading(), $this->record ?? null);
    }

    public function infolist(Schema $schema): Schema
    {
        return self::callStaticLunarHook('extendsInfolist', $this->getDefaultInfolist($schema));
    }

    protected function getDefaultInfolist(Schema $schema): Schema
    {
        return $schema;
    }
}
