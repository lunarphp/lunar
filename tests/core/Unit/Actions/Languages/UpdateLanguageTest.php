<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Languages\UpdateLanguage;
use Lunar\Core\Exceptions\LanguageActionException;
use Lunar\Core\Models\Language;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the language attributes', function () {
    $language = Language::factory()->create(['name' => 'Old Name', 'default' => false]);

    app(UpdateLanguage::class)->execute($language, ['name' => 'New Name']);

    $this->assertDatabaseHas('lunar_languages', [
        'id' => $language->id,
        'name' => 'New Name',
    ]);
});

test('promoting to default demotes the previous default', function () {
    $previous = Language::factory()->create(['default' => true]);
    $language = Language::factory()->create(['default' => false]);

    app(UpdateLanguage::class)->execute($language, ['default' => true]);

    expect($previous->refresh()->default)->toBeFalse()
        ->and($language->refresh()->default)->toBeTrue();
});

test('refuses to unset the default flag directly', function () {
    $language = Language::factory()->create(['default' => true]);

    expect(fn () => app(UpdateLanguage::class)->execute($language, ['default' => false]))
        ->toThrow(LanguageActionException::class);
});
