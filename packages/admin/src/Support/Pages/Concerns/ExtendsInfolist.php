<?php

namespace Lunar\Admin\Support\Pages\Concerns;

use Filament\Schemas\Schema;

trait ExtendsInfolist
{
    public function infolist(Schema $schema): Schema
    {
        return self::callStaticLunarHook('extendsInfolist', $this->getDefaultInfolist($schema));
    }

    protected function getDefaultInfolist(Schema $schema): Schema
    {
        return $schema;
    }
}
