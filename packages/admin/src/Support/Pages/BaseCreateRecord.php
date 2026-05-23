<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\Support\Concerns\CallsHooks;

abstract class BaseCreateRecord extends CreateRecord
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

    protected function getDefaultFormActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [
            ...parent::getFormActions(),
            ...$this->callLunarHook('formActions', $this->getDefaultFormActions()),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return self::callLunarHook('extendForm', $this->getDefaultForm($schema));
    }

    public function getDefaultForm(Schema $schema): Schema
    {
        return static::getResource()::form($schema);
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->callLunarHook('beforeCreate', $data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data = $this->callLunarHook('beforeCreation', $data);

        $record = parent::handleRecordCreation($data);

        return $this->callLunarHook('afterCreation', $record, $data);
    }
}
