<?php

namespace GetCandy\Tests\Unit\Utils;

use GetCandy\Tests\TestCase;
use Illuminate\Support\Str;

/**
 * @group core.utils
 */
class StrTest extends TestCase
{
    /** @test */
    public function passing_kebab_case_string()
    {
        $this->assertEquals('foo_bar', Str::handle('foo-bar'));
    }

    /** @test */
    public function passing_sentence_string()
    {
        $this->assertEquals('foo_bar', Str::handle('foo bar'));
    }

    /** @test */
    public function passing_mixed_sentence_and_kebab_case()
    {
        $this->assertEquals('foo_bar_foo_bar', Str::handle('foo-bar foo bar'));
    }
}
