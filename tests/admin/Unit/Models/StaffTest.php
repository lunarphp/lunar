<?php

uses(\Lunar\Tests\Admin\Unit\Models\TestCase::class)
    ->group('lunar.admin.models');

test('can get full name', function () {
    $staff = \Lunar\Admin\Models\Staff::factory()->create([
        'firstname' => 'Joe',
        'lastname' => 'Bloggs',
    ]);

    expect($staff->full_name)->toBe('Joe Bloggs');
});

test('can search staff by name', function () {
    \Lunar\Admin\Models\Staff::factory()->create([
        'firstname' => 'Joe',
        'lastname' => 'Bloggs',
    ]);

    \Lunar\Admin\Models\Staff::factory()->create([
        'firstname' => 'Tim',
        'lastname' => 'Bloggs',
    ]);

    \Lunar\Admin\Models\Staff::factory()->create([
        'firstname' => 'Bill',
        'lastname' => 'Chance',
    ]);

    expect(\Lunar\Admin\Models\Staff::search('Bloggs')->get())->toHaveCount(2)
        ->and(\Lunar\Admin\Models\Staff::search('Bill')->get())->toHaveCount(1)
        ->and(\Lunar\Admin\Models\Staff::search('Joe Bloggs')->get())->toHaveCount(1);
});

