<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

abstract class BaseEditRecord extends EditRecord
{
    use Concerns\ExtendsFooterWidgets;
    use Concerns\ExtendsFormActions;
    use Concerns\ExtendsForms;
    use Concerns\ExtendsHeaderActions;
    use Concerns\ExtendsHeaderWidgets;
    use Concerns\ExtendsHeadings;
    use \Lunar\Admin\Support\Concerns\CallsHooks;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->callLunarHook('beforeFill', $data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->callLunarHook('beforeSave', $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data = $this->callLunarHook('beforeUpdate', $data, $record);

        $record = parent::handleRecordUpdate($record, $data);

        return $this->callLunarHook('afterUpdate', $record, $data);
    }

    public function afterSave()
    {
        sync_with_search(
            $this->getRecord()
        );
    }
}
