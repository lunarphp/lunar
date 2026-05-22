<?php

namespace Lunar\Filament\Tables\Actions\Collections;

use Filament\Actions\CreateAction;
use Filament\Tables\Table;
use Lunar\Filament\Forms\Components\TranslatedText;
use Lunar\Filament\Support\Concerns\CreatesChildCollections;

/**
 * Creates a child collection inside a Filament table relationship — the
 * parent is read from `$table->getRelationship()->getParent()`, so the
 * action must be mounted on a table whose relationship yields a Collection.
 *
 * For the standalone (id-argument) variant used by the admin's collection
 * tree widget, see
 * {@see \Lunar\Admin\Support\Actions\Collections\CreateChildCollection}.
 */
class CreateChildCollection extends CreateAction
{
    use CreatesChildCollections;

    public function setUp(): void
    {
        parent::setUp();

        $this->action(function (array $arguments, array $data, Table $table): void {
            $this->createChildCollection(
                $table->getRelationship()->getParent(),
                $data['name']
            );

            $this->success();
        });

        $this->schema([
            TranslatedText::make('name')->required(),
        ]);

        $this->createAnother(false);

        $this->label(
            __('lunar-filament::collection.pages.children.actions.create_child.label')
        );
    }
}
