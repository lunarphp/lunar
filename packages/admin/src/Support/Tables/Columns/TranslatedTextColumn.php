<?php

namespace Lunar\Admin\Support\Tables\Columns;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

class TranslatedTextColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $name = $this->getName();

        $this->formatStateUsing(static function (Model $record) use ($name) {
            return $record->translate($name);
        });
    }
}
