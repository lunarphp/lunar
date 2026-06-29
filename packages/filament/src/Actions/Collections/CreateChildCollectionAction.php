<?php

namespace Lunar\Filament\Actions\Collections;

use Filament\Actions\CreateAction;
use Lunar\Core\Contracts\Actions\Collections\CreatesChildCollection;
use Lunar\Core\Models\Collection;
use Lunar\Filament\Forms\Components\TranslatedText;

/**
 * Creates a child collection from an explicit `id` argument supplied by the
 * caller — paired with widgets/tools (e.g. a tree view) that sit outside a
 * Filament table relationship and resolve the parent collection themselves.
 *
 * For the in-table-relationship variant used by Filament resource pages,
 * see {@see CreateChildCollection}.
 */
class CreateChildCollectionAction extends CreateAction
{
    public static function getDefaultName(): ?string
    {
        return 'create_child_collection';
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->action(function (array $arguments, array $data): void {
            $parent = Collection::find($arguments['id']);

            app(CreatesChildCollection::class)->execute(parent: $parent, name: $data['name']);

            $this->success();
        });

        $this->schema([
            TranslatedText::make('name')->required(),
        ]);

        $this->label(__('lunar-filament::actions.collections.create_child.label'));
        $this->createAnother(false);
        $this->modalHeading(__('lunar-filament::actions.collections.create_child.label'));
        $this->successNotificationTitle(__('lunar-filament::actions.collections.create_child.notification.success'));
    }
}
