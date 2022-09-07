<?php

namespace GetCandy\Hub\Tables;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\LivewireTables\Components\Table;

abstract class GetCandyTable extends Table
{
    use Notifies;

    public $translationNamespace = 'adminhub';
}
