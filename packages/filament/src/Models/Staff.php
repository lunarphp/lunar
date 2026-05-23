<?php

namespace Lunar\Filament\Models;

use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Lunar\Core\Models\Staff as CoreStaff;
use Lunar\Filament\Concerns\HasFilamentStaffSupport;

class Staff extends CoreStaff implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasName
{
    use HasFilamentStaffSupport;
}
