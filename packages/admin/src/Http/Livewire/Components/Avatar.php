<?php

namespace Lunar\Hub\Http\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Avatar extends Component
{
    /**
     * The users avatar url.
     *
     * @var string
     */
    public $avatar;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'hub.staff.avatar.updated' => 'updateAvatar',
    ];

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->avatar = Auth::guard('staff')->user()->gravatar;
    }

    public function updateAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('adminhub::livewire.components.avatar')
            ->layout('adminhub::layouts.base');
    }
}
