<?php

namespace Lunar\Filament\Actions\Collections;

use Filament\Actions\CreateAction;
use Filament\Schemas\Schema;
use Lunar\Core\Contracts\Actions\Collections\CreatesRootCollection;
use Lunar\Filament\Forms\Components\TranslatedText;

class CreateRootCollectionAction extends CreateAction
{
    public static function getDefaultName(): ?string
    {
        return 'create_root_collection';
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->action(function (array $arguments, Schema $schema): void {
            $model = $this->getModel();

            $record = $this->process(fn (array $data) => app(CreatesRootCollection::class)->execute(
                collectionGroupId: $data['collection_group_id'],
                name: $data['name'],
            ));

            $this->record($record);
            $schema->model($record);

            if ($arguments['another'] ?? false) {
                $this->callAfter();
                $this->sendSuccessNotification();

                $this->record(null);

                $schema->model($model);

                $schema->fill();

                $this->halt();

                return;
            }

            $this->success();
        });

        $this->schema([
            TranslatedText::make('name')
                ->label(__('lunar-filament::collection.form.name.label'))
                ->required(),
        ]);

        $this->label(__('lunar-filament::actions.collections.create_root.label'));
        $this->modalHeading(__('lunar-filament::actions.collections.create_root.label'));
        $this->successNotificationTitle(__('lunar-filament::actions.collections.create_root.notification.success'));
    }
}
