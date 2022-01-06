<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\Hub\Http\Livewire\Components\Authentication\PasswordReset;
use GetCandy\Hub\Mail\ResetPasswordEmail;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * @group hub.auth
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_send_reset_password_email()
    {
        Staff::factory()->create([
            'email' => 'test@domain.com',
        ]);

        Mail::fake();

        Livewire::test(PasswordReset::class)
            ->set('email', 'test@domain.com')
            ->assertSet('token', null)
            ->assertDontSee('password')
            ->assertDontSee('password_confirmation')
            ->call('process');

        Mail::assertSent(ResetPasswordEmail::class);
    }

    /** @test */
    public function can_reset_password()
    {
        $staff = Staff::factory()->create([
            'email' => 'test@domain.com',
        ]);

        $token = Str::random();

        Cache::shouldReceive('get')
                    ->once()
                    ->with('hub.password.reset.'.$staff->id)
                    ->andReturn($token);

        Cache::shouldReceive('forget')
                    ->once()
                    ->andReturn(true);

        Livewire::test(PasswordReset::class)
            ->set('token', encrypt($staff->id.'|'.$token))
            ->assertSee('password')
            ->assertSee('password_confirmation')
            ->assertDontSee('email')
            ->set('password', 'foo')
            ->call('process')
            ->assertHasErrors([
                'password' => 'min',
            ])
            ->set('password', 'foobarfoo')
            ->set('password_confirmation', 'bar')
            ->call('process')
            ->assertHasErrors([
                'password' => 'confirmed',
            ])
            ->set('password_confirmation', 'foobarfoo')
            ->call('process')
            ->assertHasNoErrors()
            ->assertRedirect('/hub');

        $this->assertTrue(Hash::check('foobarfoo', $staff->refresh()->password));
    }

    /** @test */
    public function cant_reset_using_invalid_token()
    {
        $staff = Staff::factory()->create([
            'email' => 'test@domain.com',
        ]);

        $token = Str::random();

        Livewire::test(PasswordReset::class)
            ->set('token', encrypt(($staff->id + 1).'|'.$token))
            ->set('password', 'foobarfoo')
            ->set('password_confirmation', 'foobarfoo')
            ->call('process')
            ->assertDispatchedBrowserEvent('notify')
            ->assertNoRedirect();

        $this->assertFalse(Hash::check('foobarfoo', $staff->refresh()->password));
    }

    /** @test */
    public function cant_reset_using_another_staffs_token()
    {
        $staffA = Staff::factory()->create([
            'email' => 'testa@domain.com',
        ]);

        $staffB = Staff::factory()->create([
            'email' => 'testb@domain.com',
        ]);

        $token = Str::random();

        Livewire::test(PasswordReset::class)
            ->set('token', encrypt($staffB->id.'|'.$token))
            ->set('password', 'foobarfoo')
            ->set('password_confirmation', 'foobarfoo')
            ->call('process')
            ->assertDispatchedBrowserEvent('notify')
            ->assertNoRedirect();

        $this->assertFalse(Hash::check('foobarfoo', $staffA->refresh()->password));
    }
}
