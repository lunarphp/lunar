<?php

namespace Lunar\Admin\Support\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Facades\App;
use Livewire\Attributes\On;
use Lunar\Admin\Support\Concerns;

#[On('refresh-relation-manager')]
class BaseRelationManager extends RelationManager
{
    use Concerns\CallsHooks;
    use Concerns\RelationManagers\ExtendsForms;
    use Concerns\RelationManagers\ExtendsTables;

    protected function getForms(): array
    {
        $forms = parent::getForms();

        if (App::runningUnitTests() && !in_array('form', $forms)) {
            // initialize the form when running tests, so we can run assertions on it
            $forms[] = 'form';
        }

        return $forms;
    }
}
