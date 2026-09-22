<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Core\Stubs\TestStaff;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('creates an admin staff account from options', function () {
    $this->artisan('lunar:create-admin', [
        '--firstname' => 'Ada',
        '--lastname' => 'Lovelace',
        '--email' => 'ada@example.com',
        '--password' => 'top-secret',
    ])->assertSuccessful();

    $staff = Staff::where('email', 'ada@example.com')->first();

    expect($staff)->not->toBeNull()
        ->and($staff->admin)->toBeTrue()
        ->and($staff->first_name)->toBe('Ada')
        ->and($staff->last_name)->toBe('Lovelace')
        ->and(Hash::check('top-secret', $staff->password))->toBeTrue();
});

test('prompts for any detail not passed as an option', function () {
    $this->artisan('lunar:create-admin')
        ->expectsQuestion('First Name', 'Grace')
        ->expectsQuestion('Last Name', 'Hopper')
        ->expectsQuestion('Email address', 'grace@example.com')
        ->expectsQuestion('Password', 'top-secret')
        ->assertSuccessful();

    $staff = Staff::where('email', 'grace@example.com')->first();

    expect($staff)->not->toBeNull()
        ->and($staff->admin)->toBeTrue();
});

test('rejects an email address already in use', function () {
    Staff::factory()->create(['email' => 'taken@example.com']);

    // Under test, a failed prompt validation aborts instead of re-asking.
    $this->artisan('lunar:create-admin')
        ->expectsQuestion('First Name', 'Ada')
        ->expectsQuestion('Last Name', 'Lovelace')
        ->expectsQuestion('Email address', 'taken@example.com')
        ->assertFailed();

    expect(Staff::where('email', 'taken@example.com')->count())->toBe(1);
});

test('rejects an --email option already in use', function () {
    Staff::factory()->create(['email' => 'taken@example.com']);

    $this->artisan('lunar:create-admin', [
        '--firstname' => 'Ada',
        '--lastname' => 'Lovelace',
        '--email' => 'taken@example.com',
        '--password' => 'top-secret',
    ])->assertFailed();

    expect(Staff::where('email', 'taken@example.com')->count())->toBe(1);
});

test('rejects an invalid --email option', function () {
    $this->artisan('lunar:create-admin', [
        '--firstname' => 'Ada',
        '--lastname' => 'Lovelace',
        '--email' => 'not-an-email',
        '--password' => 'top-secret',
    ])->assertFailed();

    expect(Staff::count())->toBe(0);
});

test('creates through a swapped lunar.staff.model', function () {
    config()->set('lunar.staff.model', TestStaff::class);

    $created = null;
    TestStaff::created(function (TestStaff $staff) use (&$created) {
        $created = $staff;
    });

    $this->artisan('lunar:create-admin', [
        '--firstname' => 'Ada',
        '--lastname' => 'Lovelace',
        '--email' => 'swapped@example.com',
        '--password' => 'top-secret',
    ])->assertSuccessful();

    expect($created)->toBeInstanceOf(TestStaff::class)
        ->and($created->admin)->toBeTrue();
});
