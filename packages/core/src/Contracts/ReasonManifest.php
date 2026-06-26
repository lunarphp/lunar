<?php

namespace Lunar\Core\Contracts;

/**
 * A key => label set of selectable "reasons" — a suggested dropdown plus a
 * label lookup. Seeded with code-level defaults; a consumer overrides or
 * extends the set from a service provider via set()/add(). The end state is a
 * store-scoped (Channel), admin-editable set — this seam keeps the reasons out
 * of process-global config so that later move needs no contract break.
 */
interface ReasonManifest
{
    /**
     * The full key => label reason set, used to populate a dropdown.
     *
     * @return array<string, string>
     */
    public function all(): array;

    /**
     * The label for a stored reason key, falling back to the raw key when it
     * is not in the set, or null when no reason is stored.
     */
    public function label(?string $key): ?string;

    /**
     * Replace the entire reason set — the override seam.
     *
     * @param  array<string, string>  $reasons
     */
    public function set(array $reasons): static;

    /**
     * Add or relabel a single reason.
     */
    public function add(string $key, string $label): static;

    /**
     * Remove one or more reasons by key — e.g. a default you don't offer.
     */
    public function forget(string ...$keys): static;
}
