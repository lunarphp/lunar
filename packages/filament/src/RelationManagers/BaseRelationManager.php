<?php

namespace Lunar\Filament\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Facades\App;
use Livewire\Attributes\On;
use Lunar\Filament\Support\Concerns\CallsHooks;
use Lunar\Filament\Support\Concerns\RelationManagers\ExtendsForms;
use Lunar\Filament\Support\Concerns\RelationManagers\ExtendsTables;

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
            $forms[] = 'form';
        }

        return $forms;
    }
}
