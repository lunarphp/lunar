<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Lunar\Hub\Http\Livewire\Components\Authentication\LoginForm;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\Stubs\User;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub.auth
 */
class LoginFormTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_log_in_to_hub()
    {
        Staff::factory()->create([
            'email' => 'test@domain.com',
        ]);

        Livewire::test(LoginForm::class)
            ->set('email', 'test@domain.com')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect('/hub');
    }

    /** @test */
    public function authentication_prevents_incorrect_login()
    {
        Staff::factory()->create([
            'email' => 'test@domain.com',
        ]);

        Livewire::test(LoginForm::class)
            ->set('email', 'test@domain.com')
            ->set('password', 'notthepassword')
            ->call('login')
            ->assertSee('The provided credentials do not match our records');
    }

    /** @test */
    public function user_model_cannot_log_into_hub()
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@domain.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        Livewire::test(LoginForm::class)
            ->set('email', 'test@domain.com')
            ->set('password', 'password')
            ->call('login')
            ->assertSee('The provided credentials do not match our records');
    }
}
