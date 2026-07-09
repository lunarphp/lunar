<?php

namespace Lunar\Panel\Tables;

use Illuminate\Database\Eloquent\Builder;

abstract class TableFilter
{
    abstract public function key(): string;

    abstract public function query(Builder $query, mixed $value): void;

    public function label(): string
    {
        return str($this->key())->replace('_', ' ')->title()->toString();
    }

    /** @return array<string, string> Label-keyed options for a select dropdown. */
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

    public function visible(): bool
    {
        if ($permission = $this->permission()) {
            return auth()->check() && auth()->user()->can($permission);
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
            'visible' => $this->visible(),
        ];
    }
}
