<?php

namespace Lunar\Admin\Support\Pages;

use Lunar\Admin\Support\Pages\Concerns\ExtendsFooterWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsFormActions;
use Lunar\Admin\Support\Pages\Concerns\ExtendsForms;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderActions;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeadings;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

abstract class BaseEditRecord extends EditRecord
{
    use ExtendsFooterWidgets;
    use ExtendsFormActions;
    use ExtendsForms;
    use ExtendsHeaderActions;
    use ExtendsHeaderWidgets;
    use ExtendsHeadings;
    use CallsHooks;

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
