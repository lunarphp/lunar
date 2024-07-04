<?php

namespace Lunar\Admin\Support\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Livewire\Attributes\On;

#[On('refresh-relation-manager')]
class BaseRelationManager extends RelationManager
{
}
