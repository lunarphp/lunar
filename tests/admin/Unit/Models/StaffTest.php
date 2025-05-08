<?php

uses(\Lunar\Tests\Admin\Unit\Models\TestCase::class)
    ->group('lunar.admin.models');

test('can get full name', function () {
    $staff = \Lunar\Admin\Models\Staff::factory()->create([
        'first_name' => 'Joe',
        'last_name' => 'Bloggs',
    ]);

    expect($staff->full_name)->toBe('Joe Bloggs');
});

test('can search staff by name', function () {
    \Lunar\Admin\Models\Staff::factory()->create([
        'first_name' => 'Joe',
        'last_name' => 'Bloggs',
    ]);

    \Lunar\Admin\Models\Staff::factory()->create([
        'first_name' => 'Tim',
        'last_name' => 'Bloggs',
    ]);

    \Lunar\Admin\Models\Staff::factory()->create([
        'first_name' => 'Bill',
        'last_name' => 'Chance',
    ]);

    expect(\Lunar\Admin\Models\Staff::search('Bloggs')->get())->toHaveCount(2)
        ->and(\Lunar\Admin\Models\Staff::search('Bill')->get())->toHaveCount(1)
        ->and(\Lunar\Admin\Models\Staff::search('Joe Bloggs')->get())->toHaveCount(1);
});

test('can get first name by old key without underscore', function () {
    $staff = \Lunar\Admin\Models\Staff::factory()->create([
        'first_name' => 'Joe',
    ]);

    expect($staff->firstname)->toBe('Joe');
});

test('can get last name by old key without underscore', function () {
    $staff = \Lunar\Admin\Models\Staff::factory()->create([
        'last_name' => 'Bloggs',
    ]);

    expect($staff->lastname)->toBe('Bloggs');
});

test('can set first name by old key without underscore', function () {
    $staff = \Lunar\Admin\Models\Staff::factory()->create([
        'first_name' => 'Joe',
    ]);

    $staff->firstname = 'Tim';

    expect($staff->firstname)->toBe('Tim');
    expect($staff->first_name)->toBe('Tim');
});

test('can set last name by old key without underscore', function () {
    $staff = \Lunar\Admin\Models\Staff::factory()->create([
        'last_name' => 'Bloggs',
    ]);

    $staff->lastname = 'Chance';

    expect($staff->lastname)->toBe('Chance');
    expect($staff->last_name)->toBe('Chance');
});
