<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Urls\DeleteUrl;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $language = Language::factory()->create(['default' => true]);
    $this->brand = Brand::factory()->create();

    $this->default = $this->brand->urls()->create([
        'language_id' => $language->id,
        'slug' => 'stark',
        'default' => true,
    ]);

    $this->other = $this->brand->urls()->create([
        'language_id' => Language::factory()->create(['code' => 'de'])->id,
        'slug' => 'stark-de',
        'default' => false,
    ]);
});

test('deletes a url', function () {
    app(DeleteUrl::class)->execute($this->other);

    $this->assertDatabaseMissing('lunar_urls', ['id' => $this->other->id]);
    expect($this->default->refresh()->default)->toBeTrue();
});

test('deleting the default promotes the first remaining url', function () {
    app(DeleteUrl::class)->execute($this->default);

    expect($this->other->refresh()->default)->toBeTrue();
});

test('deleting the last url leaves none behind', function () {
    app(DeleteUrl::class)->execute($this->other);
    app(DeleteUrl::class)->execute($this->default);

    expect($this->brand->urls()->count())->toBe(0);
});
