<?php

namespace Lunar\Hub\Tables;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\LivewireTables\Components\Table;

abstract class LunarTable extends Table
{
    use Notifies;

    public $translationNamespace = 'adminhub';
}
