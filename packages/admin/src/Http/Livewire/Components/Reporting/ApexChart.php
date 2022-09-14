<?php

namespace Lunar\Hub\Http\Livewire\Components\Reporting;

use Livewire\Component;

class ApexChart extends Component
{
    /**
     * Chart options as a Collection.
     *
     * @var \Illuminate\Support\Collection
     */
    public $options;

    /**
     * A unique key for the chart.
     *
     * @var string
     */
    public $key;

    public function mount()
    {
        $this->key = 'apex-'.md5($this->options);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.reporting.apex-charts');
    }
}
