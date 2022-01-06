<?php

namespace GetCandy\Hub\Http\Livewire;

use GetCandy\Licensing\LicenseManager;
use Livewire\Component;

class HubLicense extends Component
{
    public function render()
    {
        $license = LicenseManager::fetch('getcandy/admin-hub', []);

        // $status = $license->getStatus();

        return view('adminhub::livewire.hub-license', [
            'unlicensed'  => false,
            'development' => true,
            'invalid'     => true,
        ])->layout('adminhub::layouts.app');
    }
}
