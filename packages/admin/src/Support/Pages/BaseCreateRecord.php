<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Admin\Support\Pages\Concerns\ExtendsFooterWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsFormActions;
use Lunar\Admin\Support\Pages\Concerns\ExtendsForms;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderActions;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeadings;

abstract class BaseCreateRecord extends CreateRecord
{
    use CallsHooks;
    use ExtendsFooterWidgets;
    use ExtendsFormActions;
    use ExtendsForms;
    use ExtendsHeaderActions;
    use ExtendsHeaderWidgets;
    use ExtendsHeadings;

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
