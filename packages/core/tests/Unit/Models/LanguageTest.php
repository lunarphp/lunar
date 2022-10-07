<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Language;
use Lunar\Tests\TestCase;

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
}
