<?php

namespace Lunar\Panel\Tables;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Panel\Support\Position;

abstract class TableAction
{
    abstract public function key(): string;

    abstract public function label(): string;

    public function position(): Position
    {
        return Position::last();
    }

    /**
     * The action's target URL for a given row record. Row actions build a
     * per-row URL from the record (`route('panel.customers.edit', $record)`);
     * static actions ignore it. Null omits the action from that row.
     */
    public function url(mixed $record = null): ?string
    {
        return null;
    }

    public function method(): string
    {
        return 'post';
    }

    /** Render as an inline button (true) or collapse into the row's ellipsis menu (false). */
    public function primary(): bool
    {
        return false;
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

    /**
     * The static descriptor shared once per table. The per-row target URL is
     * resolved separately via {@see url()} and merged into each row's payload.
     *
     * @return array<string, mixed>
     */
    final public function toArray(): array
    {
        return [
            'key' => $this->key(),
            'label' => $this->label(),
            'icon' => $this->icon(),
            'method' => $this->method(),
            'primary' => $this->primary(),
            'confirmation' => $this->confirmationMessage(),
            'position' => $this->position()->toArray(),
        ];
    }

    public function icon(): ?string
    {
        return null;
    }
}
