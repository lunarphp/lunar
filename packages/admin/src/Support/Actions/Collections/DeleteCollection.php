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

        $this->action(function (): void {
            $result = $this->process(function (Collection $record) {
                $record->customerGroups()->detach();
                return $record->delete();
            });

            if (! $result) {
                $this->failure();

                return;
            }

            $this->success();
        });

        $this->label(
            __('lunarpanel::actions.collections.delete.label')
        );
    }
}
