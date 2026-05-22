<?php

namespace Lunar\Filament\Support\Concerns\RelationManagers;

use Filament\Schemas\Schema;

trait ExtendsForms
{
    public function form(Schema $schema): Schema
    {
        return self::callLunarHook('extendForm', $this->getDefaultForm($schema));
    }

    public function getDefaultForm(Schema $schema): Schema
    {
        return $schema;
    }
}
