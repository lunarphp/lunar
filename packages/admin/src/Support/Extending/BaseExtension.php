<?php

namespace Lunar\Admin\Support\Extending;

use Filament\Forms\Form;
use Filament\Tables\Table;

abstract class BaseExtension
{
    public function headerActions(array $actions): array
    {
        return $actions;
    }
    public function extendTable(Table $table): Table
    {
        return $table;
    }

    public function extendForm(Form $form): Form
    {
        return $form;
    }
}
