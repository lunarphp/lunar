<?php

namespace Lunar\Admin\Support\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Livewire\Attributes\On;

class BaseRelationManager extends RelationManager
{
    #[On('refresh-relation-manager')]
    public function refreshRelationManager()
    {
        // force a re-render
    }
}
