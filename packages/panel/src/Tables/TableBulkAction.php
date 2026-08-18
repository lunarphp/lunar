<?php

namespace Lunar\Panel\Tables;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Panel\Support\Position;

abstract class TableBulkAction
{
    abstract public function key(): string;

    abstract public function label(): string;

    public function position(): Position
    {
        return Position::last();
    }

    public function icon(): ?string
    {
        return null;
    }

    /** The endpoint the selected row ids are submitted to. */
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
            'icon' => $this->icon(),
            'url' => $this->url(),
            'method' => $this->method(),
            'confirmation' => $this->confirmationMessage(),
            'position' => $this->position()->toArray(),
        ];
    }
}
