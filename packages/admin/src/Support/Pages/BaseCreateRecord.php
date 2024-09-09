<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

abstract class BaseCreateRecord extends CreateRecord
{
    use Concerns\ExtendsFooterWidgets;
    use Concerns\ExtendsForms;
    use Concerns\ExtendsFormActions;
    use Concerns\ExtendsHeaderActions;
    use Concerns\ExtendsHeaderWidgets;
    use Concerns\ExtendsHeadings;
    use \Lunar\Admin\Support\Concerns\CallsHooks;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->callLunarHook('beforeCreate', $data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data = $this->callLunarHook('beforeCreation', $data);

        $record = parent::handleRecordCreation($data);

        return $this->callLunarHook('afterCreation', $record, $data);
    }
}
