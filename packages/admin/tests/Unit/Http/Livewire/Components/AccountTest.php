<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\Hub\Http\Livewire\Components\Account;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

/**
 * @group hub.account
 */
class AccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_mount_component()
    {
        $staff = Staff::factory()->create([
            'email'     => 'test@domain.com',
            'firstname' => 'Bob',
            'lastname'  => 'Smith',
        ]);

        Livewire::test(Account::class, ['staff' => $staff])
            ->assertSet('staff.firstname', 'Bob')
            ->assertSet('staff.lastname', 'Smith')
            ->assertSet('staff.email', 'test@domain.com')
            ->assertSet('password', null)
            ->assertSet('newPassword', null);
    }

    /** @test */
    public function can_update_details()
    {
        $staff = Staff::factory()->create([
            'email'     => 'test@domain.com',
            'firstname' => 'Bob',
            'lastname'  => 'Smith',
        ]);

        Livewire::test(Account::class, ['staff' => $staff])
            ->assertSet('staff.firstname', 'Bob')
            ->assertSet('staff.lastname', 'Smith')
            ->assertSet('staff.email', 'test@domain.com')
            ->set('staff.firstname', 'Billy')
            ->set('staff.lastname', 'Rabbit')
            ->set('staff.email', 'testTwo@domain.com')
            ->assertSet('staff.firstname', 'Billy')
            ->assertSet('staff.lastname', 'Rabbit')
            ->assertSet('staff.email', 'testTwo@domain.com')
            ->call('save')
            ->assertHasNoErrors();

        $staff = $staff->refresh();

        $this->assertEquals('Billy', $staff->firstname);
        $this->assertEquals('Rabbit', $staff->lastname);
        $this->assertEquals('testTwo@domain.com', $staff->email);
    }

    /** @test */
    public function can_save_without_changing_email()
    {
        $staff = Staff::factory()->create([
            'email'     => 'test@domain.com',
            'firstname' => 'Bob',
            'lastname'  => 'Smith',
        ]);

        Livewire::test(Account::class, ['staff' => $staff])
            ->assertSet('staff.firstname', 'Bob')
            ->assertSet('staff.lastname', 'Smith')
            ->set('staff.firstname', 'Billy')
            ->set('staff.lastname', 'Rabbit')
            ->assertSet('staff.firstname', 'Billy')
            ->assertSet('staff.lastname', 'Rabbit')
            ->call('save')
            ->assertHasNoErrors();
    }

    /** @test */
    public function password_doesnt_update_if_unchanged()
    {
        $staff = Staff::factory()->create([
            'email'    => 'test@domain.com',
            'password' => Hash::make('password'),
        ]);

        Livewire::actingAs($staff, 'staff')->test(Account::class, ['staff' => $staff])
          ->set('staff.firstname', 'Billy')
          ->set('staff.lastname', 'Rabbit')
          ->call('save')
          ->assertHasNoErrors();

        $staff = $staff->refresh();

        $this->assertTrue(
            Hash::check('password', $staff->password)
        );
    }

    /** @test */
    public function cannot_use_an_existing_email()
    {
        $staff = Staff::factory()->create([
            'email' => 'test@domain.com',
        ]);

        Staff::factory()->create([
            'email' => 'existing@domain.com',
        ]);

        Livewire::test(Account::class, ['staff' => $staff])
            ->set('staff.email', 'existing@domain.com')
            ->call('save')
            ->assertHasErrors([
                'staff.email' => 'unique',
            ]);

        $staff = $staff->refresh();

        $this->assertEquals('test@domain.com', $staff->email);
    }

    /** @test */
    public function can_update_password()
    {
        $staff = Staff::factory()->create([
            'email'    => 'test@domain.com',
            'password' => Hash::make('password'),
        ]);

        Livewire::actingAs($staff, 'staff')->test(Account::class, ['staff' => $staff])
            ->set('currentPassword', 'password')
            ->set('password', 'newpassword')
            ->call('save')
            ->assertHasNoErrors();

        $staff = $staff->refresh();

        $this->assertTrue(
            Hash::check('newpassword', $staff->password)
        );
    }

    /** @test */
    public function cant_update_password_if_current_is_wrong()
    {
        $staff = Staff::factory()->create([
            'email'    => 'test@domain.com',
            'password' => Hash::make('password'),
        ]);

        Livewire::actingAs($staff, 'staff')->test(Account::class, ['staff' => $staff])
            ->set('currentPassword', 'wrongpassword')
            ->set('password', 'newpassword')
            ->call('save')
            ->assertHasErrors([
                'currentPassword' => 'current_password',
            ]);

        $staff = $staff->refresh();

        $this->assertTrue(
            Hash::check('password', $staff->password)
        );
    }
}
