<?php

namespace GetCandy\Hub\Http\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CurrentStaffName extends Component
{
    /**
     * The staff members full name.
     *
     * @var string
     */
    public $fullName;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'hub.staff.name.updated' => 'updateFullName'
    ];

    /**
     * Specify additional classes.
     * @var null|string
     */
    public $class;

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->fullName = Auth::guard('staff')->user()->fullName;
    }

    /**
     * Update the staff members full name for display.
     *
     * @param string $fullName
     * @return void
     */
    public function updateFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('adminhub::livewire.components.current-staff-name')
            ->layout('adminhub::layouts.base');
    }
}