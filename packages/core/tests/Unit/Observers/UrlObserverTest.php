<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Models\Language;
use Lunar\Models\Url;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can only have one default per language', function () {
    $langA = Language::factory()->create();
    $langB = Language::factory()->create();

    $default = Url::factory()->create([
        'slug' => 'foo-bar',
        'language_id' => $langA->id,
        'default' => true,
    ]);

    expect($default->default)->toBeTrue();

    $newDefault = Url::factory()->create([
        'slug' => 'foo-bar-new',
        'language_id' => $langA->id,
        'default' => true,
    ]);

    $diffLang = Url::factory()->create([
        'slug' => 'foo-bar-lang-ex',
        'language_id' => $langB->id,
        'default' => true,
    ]);

    expect($default->refresh()->default)->toBeFalse();
    expect($newDefault->refresh()->default)->toBeTrue();
    expect($diffLang->refresh()->default)->toBeTrue();
});

test('new default is selected when current is deleted', function () {
    $langA = Language::factory()->create();
    $langB = Language::factory()->create();

    $default = Url::factory()->create([
        'slug' => 'foo-bar',
        'language_id' => $langA->id,
        'default' => true,
    ]);

    $newDefault = Url::factory()->create([
        'slug' => 'foo-bar-new',
        'language_id' => $langA->id,
        'default' => false,
    ]);

    $diffLang = Url::factory()->create([
        'slug' => 'foo-bar-lang-ex',
        'language_id' => $langB->id,
        'default' => false,
    ]);

    $default->delete();

    expect($newDefault->refresh()->default)->toBeTrue();
    expect($diffLang->refresh()->default)->toBeFalse();
});
