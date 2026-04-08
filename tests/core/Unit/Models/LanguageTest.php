<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Language;
use Lunar\Models\Url;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can make a language', function () {
    $language = Language::factory()->create([
        'code' => 'fr',
        'name' => 'Français',
        'default' => true,
    ]);

    expect($language->code)->toEqual('fr');
    expect($language->name)->toEqual('Français');
    expect($language->default)->toBeTrue();
});

test('can cleanup relations on deletion', function () {
    $language = Language::factory()->create([
        'code' => 'fr',
        'name' => 'Français',
        'default' => true,
    ]);

    Url::factory()->create([
        'language_id' => $language->id,
    ]);

    $this->assertDatabaseHas((new Url)->getTable(), [
        'language_id' => $language->id,
    ]);

    $language->delete();

    $this->assertDatabaseMissing((new Url)->getTable(), [
        'language_id' => $language->id,
    ]);
});
