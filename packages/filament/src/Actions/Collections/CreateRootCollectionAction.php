<?php

namespace Lunar\Filament\Actions\Collections;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Lunar\Core\Actions\Collections\CreateRootCollection;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Collection;
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

            $record = $this->process(fn (array $data) => CreateRootCollection::run(
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

        $attribute = Attribute::where('attribute_type', '=', Collection::morphName())
            ->where('handle', '=', 'name')->first();

        $formInput = TextInput::class;

        if ($attribute?->type === \Lunar\Core\FieldTypes\TranslatedText::class) {
            $formInput = TranslatedText::class;
        }

        $this->schema([
            $formInput::make('name')
                ->label(__('lunar-filament::collection.form.name.label'))
                ->required(),
        ]);

        $this->label(__('lunar-filament::actions.collections.create_root.label'));
        $this->modalHeading(__('lunar-filament::actions.collections.create_root.label'));
        $this->successNotificationTitle(__('lunar-filament::actions.collections.create_root.notification.success'));
    }
}
