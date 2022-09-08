<?php

namespace GetCandy\LivewireTables\Components\Concerns;

use Closure;
use Illuminate\Support\Str;

trait HasEloquentRelationships
{
    protected function getRelationshipColumn(): string
    {
        return (string) Str::of($this->field)->afterLast('.');
    }

    protected function getRelationshipName(): string
    {
        return (string) Str::of($this->field)->beforeLast('.');
    }
}
