<?php

namespace Lunar\Filament\Actions\Collections;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Actions\Collections\MoveCollection;
use Lunar\Core\Exceptions\CollectionActionException;
use Lunar\Core\Models\Collection;

class MoveCollectionAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'move_collection';
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->record(fn (array $arguments) => Collection::find($arguments['id']));

        $this->action(function (array $data, Model $record): void {
            $target = isset($data['target_id']) ? Collection::find($data['target_id']) : null;

            try {
                MoveCollection::run(collection: $record, target: $target);
            } catch (CollectionActionException $exception) {
                $this->failureNotificationTitle($exception->getMessage());
                $this->failure();
                $this->halt();

                return;
            }

            $this->success();
        });

        $this->label(__('lunar-filament::actions.collections.move.label'));
        $this->successNotificationTitle(__('lunar-filament::actions.collections.move.notification.success'));
    }
}
