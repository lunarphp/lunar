<?php

namespace Lunar\Admin\Support\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Facades\App;
use Livewire\Attributes\On;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Admin\Support\Concerns\RelationManagers\ExtendsForms;
use Lunar\Admin\Support\Concerns\RelationManagers\ExtendsTables;

#[On('refresh-relation-manager')]
class BaseRelationManager extends RelationManager
{
    use CallsHooks;
    use ExtendsForms;
    use ExtendsTables;

    protected function getForms(): array
    {
        $forms = parent::getForms();

        if (App::runningUnitTests() && ! in_array('form', $forms)) {
            // initialize the form when running tests, so we can run assertions on it
            $forms[] = 'form';
        }

        return $forms;
    }
}
