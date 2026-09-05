<?php

namespace Lunar\Panel\Search;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Panel\Support\Position;

/**
 * A static verb the global search offers alongside record results — the create
 * flows and other destinations staff reach for by name rather than by record.
 * Resolved once per request and shared to the frontend, so the palette can
 * filter commands without a round trip.
 */
abstract class SearchCommand
{
    abstract public function key(): string;

    /** The searchable, translated label, e.g. 'Create product'. */
    abstract public function label(): string;

    abstract public function url(): string;

    public function icon(): string
    {
        return 'plus';
    }

    /** Manifest permission handle gating this command; null makes it always visible. */
    public function permission(): ?string
    {
        return null;
    }

    public function position(): Position
    {
        return Position::last();
    }

    public function visible(?Authenticatable $user = null): bool
    {
        if ($permission = $this->permission()) {
            return $user !== null && $user->can($permission);
        }

        return true;
    }

    /** @return array{key: string, label: string, url: string, icon: string} */
    final public function toArray(): array
    {
        return [
            'key' => $this->key(),
            'label' => $this->label(),
            'url' => $this->url(),
            'icon' => $this->icon(),
        ];
    }
}
