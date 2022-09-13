<?php

namespace Lunar\Hub\Http\Livewire;

use Livewire\Component;
use Lunar\Licensing\LicenseManager;

class HubLicense extends Component
{
    public function render()
    {
        $license = LicenseManager::fetch('lunar/admin-hub', []);

        // $status = $license->getStatus();

        return view('adminhub::livewire.hub-license', [
            'unlicensed'  => false,
            'development' => true,
            'invalid'     => true,
        ])->layout('adminhub::layouts.app');
    }
}
