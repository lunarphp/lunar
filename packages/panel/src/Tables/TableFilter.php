<?php

namespace Lunar\Panel\Tables;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Panel\Support\Position;

abstract class TableFilter
{
    abstract public function key(): string;

    abstract public function query(Builder $query, mixed $value): void;

    public function position(): Position
    {
        return Position::last();
    }

    public function label(): string
    {
        return str($this->key())->replace('_', ' ')->title()->toString();
    }

    /**
     * Dropdown options as [submitted value => label]. A filter with no
     * options is not rendered by the generic toolbar dropdown (reserved for
     * component-rendered filters via {@see component()}).
     *
     * @return array<string|int, string>
     */
    public function options(): array
    {
        return [];
    }

    public function component(): ?string
    {
        return null;
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

    /** @return array<string, mixed> */
    final public function toArray(): array
    {
        return [
            'key' => $this->key(),
            'label' => $this->label(),
            'component' => $this->component(),
            'options' => $this->options(),
            'position' => $this->position()->toArray(),
        ];
    }
}
