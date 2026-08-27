<?php

namespace Lunar\Admin\Filament\Resources\AttributeResource\Pages;

use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\AttributeResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;
use Lunar\Core\Contracts\Actions\Attributes\CreatesAttribute;

class CreateAttribute extends BaseCreateRecord
{
    protected static string $resource = AttributeResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data = $this->callLunarHook('beforeCreation', $data);

        $record = app(CreatesAttribute::class)->execute($data);

        return $this->callLunarHook('afterCreation', $record, $data);
    }
}
