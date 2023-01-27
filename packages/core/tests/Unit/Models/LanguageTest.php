<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Language;
use Lunar\Models\Url;
use Lunar\Tests\TestCase;

/**
 * @group lunar.models
 */
class LanguageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_language()
    {
        $language = Language::factory()->create([
            'code' => 'fr',
            'name' => 'Français',
            'default' => true,
        ]);

        $this->assertEquals('fr', $language->code);
        $this->assertEquals('Français', $language->name);
        $this->assertTrue($language->default);
    }

    /** @test */
    public function can_cleanup_relations_on_deletion()
    {
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
    }
}
