<?php

namespace Lunar\Panel\Tables;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\Support\ColumnType;

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

    public function visible(?Authenticatable $user = null): bool
    {
        if ($permission = $this->permission()) {
            return $user !== null && $user->can($permission);
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
        ];
    }
}
