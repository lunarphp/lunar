<?php

namespace Lunar\Admin\Support\Extending;

use Filament\Forms\Form;
use Filament\Tables\Table;

abstract class RelationManagerExtension extends BaseExtension
{
    public function extendForm(Form $form): Form
    {
        return $form;
    }

    public function extendTable(Table $table): Table
    {
        return $table;
    }
}
