<?php

namespace Lunar\Admin\Support\Tables\Actions\Collections;

use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Table;
use Lunar\Admin\Support\Actions\Traits\CreatesChildCollections;
use Lunar\Admin\Support\Forms\Components\TranslatedText;

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

        $this->form([
            TranslatedText::make('name')->required(),
        ]);

        $this->createAnother(false);

        $this->label(
            __('lunarpanel::collection.pages.children.actions.create_child.label')
        );
    }
}
