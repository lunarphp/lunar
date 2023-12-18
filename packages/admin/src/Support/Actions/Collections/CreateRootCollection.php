<?php

namespace Lunar\Admin\Support\Actions\Collections;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Lunar\Facades\DB;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\Collection;
use Lunar\Models\Language;

class CreateRootCollection extends CreateAction
{
    public function setUp(): void
    {
        parent::setUp();

        $this->action(function (array $arguments, Form $form): void {
            $model = $this->getModel();

            DB::beginTransaction();

            $record = $this->process(function (array $data) {
                $attribute = Attribute::whereHandle('name')->whereAttributeType(Collection::class)->first();
                $nameValue = $data['name'];

                $fieldType = $attribute->type;

                if ($fieldType == TranslatedText::class) {
                    $language = Language::getDefault();
                    $nameValue = collect([
                        $language->code => $data['name'],
                    ]);
                }

                return Collection::create([
                    'collection_group_id' => $data['collection_group_id'],
                    'attribute_data' => [
                        'name' => new $fieldType($nameValue),
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
            TextInput::make('name')->required(),
        ]);

        $this->label(
            __('lunarpanel::actions.collections.create_root.label')
        );

        $this->modalHeading(
            __('lunarpanel::actions.collections.create_root.label')
        );
    }
}
