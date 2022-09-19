<?php

namespace Lunar\Hub\Tests\Feature\Http\Livewire\Pages\Authentication;

use Lunar\Hub\Tests\TestCase;

/**
 * @group hub.auth
 */
class PasswordResetTest extends TestCase
{
    /** @test */
    public function page_contains_livewire_component()
    {
        $this->get('/hub/password-reset')->assertSeeLivewire('hub.components.password-reset');
    }
}
