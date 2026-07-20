<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Languages\CreateLanguage;
use Lunar\Core\Models\Language;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a language with the given attributes', function () {
    $language = app(CreateLanguage::class)->execute([
        'code' => 'fr',
        'name' => 'French',
    ]);

    expect($language)->toBeInstanceOf(Language::class);

    $this->assertDatabaseHas('lunar_languages', [
        'id' => $language->id,
        'code' => 'fr',
        'name' => 'French',
    ]);
});

test('demotes the previous default when created as default', function () {
    $previous = Language::factory()->create(['default' => true]);

    $language = app(CreateLanguage::class)->execute([
        'code' => 'fr',
        'name' => 'French',
        'default' => true,
    ]);

    expect($previous->refresh()->default)->toBeFalse()
        ->and($language->refresh()->default)->toBeTrue();
});
