<?php

namespace Lunar\Panel\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Panel\Support\Position;

/**
 * A header action on any panel page — a labelled, permission-gated, optionally
 * confirmed button or menu item. Record pages resolve its URL from the bound
 * record ($context); listing pages resolve it with no context. Primary actions
 * render as inline header buttons; the rest collapse into the page's ellipsis.
 */
abstract class PageAction
{
    abstract public function key(): string;

    abstract public function label(): string;

    public function icon(): ?string
    {
        return null;
    }

    public function position(): Position
    {
        return Position::last();
    }

    public function url(mixed $context = null): ?string
    {
        return null;
    }

    public function method(): string
    {
        return 'get';
    }

    /** Render as an inline header button (true) or collapse into the ellipsis (false). */
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

    public function visible(mixed $context = null, ?Authenticatable $user = null): bool
    {
        if ($permission = $this->permission()) {
            return $user !== null && $user->can($permission);
        }

        return true;
    }

    /** @return array<string, mixed> */
    final public function toArray(mixed $context = null): array
    {
        return [
            'key' => $this->key(),
            'label' => $this->label(),
            'icon' => $this->icon(),
            'url' => $this->url($context),
            'method' => $this->method(),
            'primary' => $this->primary(),
            'confirmation' => $this->confirmationMessage(),
        ];
    }
}
