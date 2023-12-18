<?php

namespace Lunar\Admin\Support\Actions\Collections;

use Filament\Actions\DeleteAction;
use Lunar\Models\Collection;

class DeleteCollection extends DeleteAction
{
    public function setUp(): void
    {
        parent::setUp();

        $this->record(function (array $arguments) {
            return Collection::find($arguments['id']);
        });

        $this->label(
            __('lunarpanel::actions.collections.delete.label')
        );
    }
}
