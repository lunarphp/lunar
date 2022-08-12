<?php

namespace GetCandy\Tests\Stubs\Models;

trait SearchableTrait
{
    protected function shouldBeSomethingElseSearchable(): bool
    {
        return false;
    }
}
