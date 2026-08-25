<?php

namespace Lunar\Admin\Filament\Resources\AttributeResource\Pages;

use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\AttributeResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Core\Contracts\Actions\Attributes\UpdatesAttribute;
use Lunar\Filament\Actions\Attributes\DeleteAttributeAction;
use Lunar\Filament\Schemas\Attribute\AttributeForm;
use Lunar\Filament\Support\Resolver;

class EditAttribute extends BaseEditRecord
{
    protected static string $resource = AttributeResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            DeleteAttributeAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = Resolver::resolve(AttributeForm::class)::mutateDataForFill($this->getRecord(), $data);

        return parent::mutateFormDataBeforeFill($data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data = $this->callLunarHook('beforeUpdate', $data, $record);

        $record = app(UpdatesAttribute::class)->execute($record, $data);

        return $this->callLunarHook('afterUpdate', $record, $data);
    }
}
