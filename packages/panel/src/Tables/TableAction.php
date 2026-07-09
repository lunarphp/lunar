<?php

namespace Lunar\Panel\Tables;

abstract class TableAction
{
    abstract public function key(): string;

    abstract public function label(): string;

    public function component(): ?string
    {
        return null;
    }

    public function url(): ?string
    {
        return null;
    }

    public function method(): string
    {
        return 'post';
    }

    public function confirmationMessage(): ?string
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
            'url' => $this->url(),
            'method' => $this->method(),
            'confirmation' => $this->confirmationMessage(),
            'visible' => $this->visible(),
        ];
    }
}
