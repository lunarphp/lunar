<?php

namespace Lunar\LivewireTables\Components\Actions;

class BulkAction extends Action
{
    /**
     * The selected id's for the action
     *
     * @var type
     */
    public array $selectedIds = [];

    /**
     * Whether to show the modal.
     */
    public bool $showModal = false;

    /**
     * {@inheritDoc}
     */
    public function getListeners()
    {
        return [
            'table.selectedRows' => 'setSelected',
            'bulkAction.complete' => 'resetState',
        ];
    }

    public function resetState()
    {
        $this->selectedIds = [];
        $this->showModal = false;
        $this->emit('bulkAction.reset');
    }

    /**
     * Set the selected ids
     *
     * @return void
     */
    public function setSelected(array $rows)
    {
        $this->selectedIds = $rows;
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('l-tables::actions.bulk', [
            'label' => $this->label,
            'selectedIds' => $this->selectedIds,
            'livewire' => $this->livewire,
        ]);
    }
}
