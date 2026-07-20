<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Languages\DeleteLanguage;
use Lunar\Core\Exceptions\LanguageActionException;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Url;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes a language without urls', function () {
    $language = Language::factory()->create(['default' => false]);

    app(DeleteLanguage::class)->execute($language);

    $this->assertDatabaseMissing('lunar_languages', ['id' => $language->id]);
});

test('refuses to delete the default language', function () {
    $language = Language::factory()->create(['default' => true]);

    expect(fn () => app(DeleteLanguage::class)->execute($language))
        ->toThrow(LanguageActionException::class);
});

test('refuses to delete a language with urls', function () {
    $language = Language::factory()->create(['default' => false]);
    Url::factory()->create(['language_id' => $language->id]);

    expect(fn () => app(DeleteLanguage::class)->execute($language))
        ->toThrow(LanguageActionException::class);

    $this->assertDatabaseHas('lunar_languages', ['id' => $language->id]);
});
