<?php

namespace Lunar\Admin\Support\Actions\Collections;

use Filament\Actions\CreateAction;
use Filament\Forms\Form;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Facades\DB;
use Lunar\Models\Attribute;
use Lunar\Models\Collection;

class CreateRootCollection extends CreateAction
{
    public function setUp(): void
    {
        parent::setUp();

        $this->action(function (array $arguments, Form $form): void {
            $model = $this->getModel();

            DB::beginTransaction();

            $record = $this->process(function (array $data) {
                $attribute = Attribute::whereHandle('name')->whereAttributeType(Collection::class)->first()->type;

                return Collection::create([
                    'collection_group_id' => $data['collection_group_id'],
                    'attribute_data' => [
                        'name' => new $attribute($data['name']),
                    ],
                ]);
            });

            DB::commit();

            $this->record($record);
            $form->model($record);

            if ($arguments['another'] ?? false) {
                $this->callAfter();
                $this->sendSuccessNotification();

                $this->record(null);

                // Ensure that the form record is anonymized so that relationships aren't loaded.
                $form->model($model);

                $form->fill();

                $this->halt();

                return;
            }

            $this->success();
        });

        $this->form([
            TranslatedText::make('name')->required(),
        ]);

        $this->label(
            __('lunarpanel::actions.collections.create_root.label')
        );

        $this->modalHeading(
            __('lunarpanel::actions.collections.create_root.label')
        );
    }
}
