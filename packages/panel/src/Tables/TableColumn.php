<?php

namespace Lunar\Panel\Tables;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Panel\Tables\Support\ColumnType;
use Lunar\Panel\Tables\Support\Position;

abstract class TableColumn
{
    abstract public function key(): string;

    abstract public function header(): string;

    public function component(): ?string
    {
        return null;
    }

    public function type(): ?ColumnType
    {
        return null;
    }

    public function position(): Position
    {
        return Position::last();
    }

    public function permission(): ?string
    {
        return null;
    }

    public function visible(): bool
    {
        if ($permission = $this->permission()) {
            return auth()->check() && auth()->user()->can($permission);
        }

        return true;
    }

    public function query(Builder $query): void {}

    /** @return array<string, mixed> */
    final public function toArray(): array
    {
        return [
            'key' => $this->key(),
            'header' => $this->header(),
            'component' => $this->component(),
            'type' => $this->type()?->toArray(),
            'position' => $this->position()->toArray(),
            'visible' => $this->visible(),
        ];
    }
}
