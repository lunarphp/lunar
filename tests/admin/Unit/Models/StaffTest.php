<?php

use Lunar\Core\Models\Staff;
use Lunar\Tests\Admin\Unit\Models\TestCase;

uses(TestCase::class)
    ->group('lunar.admin.models');

test('can get full name', function () {
    $staff = Staff::factory()->create([
        'first_name' => 'Joe',
        'last_name' => 'Bloggs',
    ]);

    expect($staff->full_name)->toBe('Joe Bloggs');
});

test('can search staff by name', function () {
    // Pin emails: scopeSearch also matches on email, so an unconstrained faker
    // email containing a search term ("joe", "bill") would inflate the counts.
    Staff::factory()->create([
        'first_name' => 'Joe',
        'last_name' => 'Bloggs',
        'email' => 'joe.bloggs@example.com',
    ]);

    Staff::factory()->create([
        'first_name' => 'Tim',
        'last_name' => 'Bloggs',
        'email' => 'tim.bloggs@example.com',
    ]);

    Staff::factory()->create([
        'first_name' => 'Bill',
        'last_name' => 'Chance',
        'email' => 'bill.chance@example.com',
    ]);

    expect(Staff::search('Bloggs')->get())->toHaveCount(2)
        ->and(Staff::search('Bill')->get())->toHaveCount(1)
        ->and(Staff::search('Joe Bloggs')->get())->toHaveCount(1);
});

test('can get first name by old key without underscore', function () {
    $staff = Staff::factory()->create([
        'first_name' => 'Joe',
    ]);

    expect($staff->firstname)->toBe('Joe');
});

test('can get last name by old key without underscore', function () {
    $staff = Staff::factory()->create([
        'last_name' => 'Bloggs',
    ]);

    expect($staff->lastname)->toBe('Bloggs');
});

test('can set first name by old key without underscore', function () {
    $staff = Staff::factory()->create([
        'first_name' => 'Joe',
    ]);

    $staff->firstname = 'Tim';

    expect($staff->firstname)->toBe('Tim');
    expect($staff->first_name)->toBe('Tim');
});

test('can set last name by old key without underscore', function () {
    $staff = Staff::factory()->create([
        'last_name' => 'Bloggs',
    ]);

    $staff->lastname = 'Chance';

    expect($staff->lastname)->toBe('Chance');
    expect($staff->last_name)->toBe('Chance');
});
