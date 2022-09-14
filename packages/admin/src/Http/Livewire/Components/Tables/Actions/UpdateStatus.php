<?php

namespace Lunar\Hub\Http\Livewire\Components\Tables\Actions;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Order;
use Livewire\Component;

class UpdateStatus extends Component
{
    use Notifies;

    /**
     * The array of selected IDs
     *
     * @var array
     */
    public array $ids = [];

    public $status = null;

    /**
     * {@inheritDoc}
     */
    public function getListeners()
    {
        return [
            'table.selectedRows' => 'setSelected',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'status' => 'required',
        ];
    }

    public function getStatusesProperty()
    {
        return config('getcandy.orders.statuses');
    }

    /**
     * Set the selected ids
     *
     * @param  array  $rows
     * @return void
     */
    public function setSelected(array $rows)
    {
        $this->ids = $rows;
    }

    /**
     * Save the updated status
     *
     * @return
     */
    public function updateStatus()
    {
        Order::whereIn('id', $this->ids)->update([
            'status' => $this->status,
        ]);

        $this->notify('Order statuses updated');
        $this->emit('bulkAction.complete');
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.tables.actions.update-status')
            ->layout('adminhub::layouts.base');
    }
}
