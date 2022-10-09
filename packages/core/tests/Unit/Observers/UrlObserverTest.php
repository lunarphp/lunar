<?php

namespace Lunar\Tests\Unit\Observers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Language;
use Lunar\Models\Url;
use Lunar\Tests\TestCase;

/**
 * @group observers
 */
class UrlObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_only_have_one_default_per_language()
    {
        $langA = Language::factory()->create();
        $langB = Language::factory()->create();

        $default = Url::factory()->create([
            'slug' => 'foo-bar',
            'language_id' => $langA->id,
            'default' => true,
        ]);

        $this->assertTrue($default->default);

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

        $this->assertFalse($default->refresh()->default);
        $this->assertTrue($newDefault->refresh()->default);
        $this->assertTrue($diffLang->refresh()->default);
    }

    /** @test */
    public function new_default_is_selected_when_current_is_deleted()
    {
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

        $this->assertTrue($newDefault->refresh()->default);
        $this->assertFalse($diffLang->refresh()->default);
    }
}
