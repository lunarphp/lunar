<?php

namespace Lunar\Panel\Slots;

use Illuminate\Contracts\Auth\Authenticatable;

class SlotRegistry
{
    /** @var Slot[] */
    protected array $slots = [];

    public function add(Slot $slot): static
    {
        $this->slots[] = $slot;

        return $this;
    }

    /**
     * Slots for one page, keyed by zone. The page prefix is the panel route
     * name without the leading "panel." — zones match when they start with
     * "{pagePrefix}:".
     *
     * @return array<string, array<int, array{component: string, props: array<string, mixed>, priority: int}>>
     */
    public function forPage(string $pagePrefix, ?Authenticatable $user = null): array
    {
        return $this->serialize(
            array_filter(
                $this->slots,
                fn (Slot $slot) => str_starts_with($slot->zone, "{$pagePrefix}:"),
            ),
            $user,
        );
    }

    /**
     * @return array<string, array<int, array{component: string, props: array<string, mixed>, priority: int}>>
     */
    public function all(?Authenticatable $user = null): array
    {
        return $this->serialize($this->slots, $user);
    }

    /**
     * @param  Slot[]  $slots
     * @return array<string, array<int, array{component: string, props: array<string, mixed>, priority: int}>>
     */
    protected function serialize(array $slots, ?Authenticatable $user): array
    {
        return collect($slots)
            ->filter(fn (Slot $slot) => $this->userCanSee($slot, $user))
            ->sortBy(fn (Slot $slot) => $slot->priority)
            ->groupBy(fn (Slot $slot) => $slot->zone)
            ->map(fn ($group) => $group->map(fn (Slot $slot) => $slot->toArray())->values()->all())
            ->all();
    }

    protected function userCanSee(Slot $slot, ?Authenticatable $user): bool
    {
        if ($slot->permission === null) {
            return true;
        }

        return $user !== null && $user->can($slot->permission);
    }
}
