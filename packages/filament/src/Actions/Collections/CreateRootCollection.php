<?php

namespace Lunar\Filament\Actions\Collections;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Collection;
use Lunar\Filament\Forms\Components\TranslatedText;

class CreateRootCollection extends CreateAction
{
    public function setUp(): void
    {
        parent::setUp();

        $this->action(function (array $arguments, Schema $schema): void {
            $model = $this->getModel();

            DB::beginTransaction();

            $record = $this->process(function (array $data) {
                $attribute = Attribute::whereHandle('name')->whereAttributeType(
                    Collection::morphName()
                )->first()->type;

                return Collection::create([
                    'collection_group_id' => $data['collection_group_id'],
                    'attribute_data' => [
                        'name' => new $attribute($data['name']),
                    ],
                ]);
            });

            DB::commit();

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

        if ($attribute?->type == \Lunar\Core\FieldTypes\TranslatedText::class) {
            $formInput = TranslatedText::class;
        }

        $this->schema([
            $formInput::make('name')
                ->label(__('lunar-filament::collection.form.name.label'))
                ->required(),
        ]);

        $this->label(
            __('lunar-filament::actions.collections.create_root.label')
        );

        $this->modalHeading(
            __('lunar-filament::actions.collections.create_root.label')
        );
    }
}
