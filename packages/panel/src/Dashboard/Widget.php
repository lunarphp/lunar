<?php

namespace Lunar\Panel\Dashboard;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Panel\Support\Position;

/**
 * A dashboard widget — an identified, permission-gated card the staff member
 * can reorder, hide, and re-add. The dashboard grid owns the card chrome
 * (header, drag handle, hide button); the widget's Vue component renders body
 * content only, receiving the payload from data() as props.
 */
abstract class Widget
{
    abstract public function key(): string;

    /** JS component name: bare for first-party widgets, namespaced ('my-addon::SalesWidget') for add-ons. */
    abstract public function component(): string;

    /** Shown in the card header and the customise dialog. */
    abstract public function label(): string;

    /** @return array<string, mixed> The Vue component's props for the given range. */
    abstract public function data(DashboardRange $range): array;

    public function description(): ?string
    {
        return null;
    }

    public function icon(): ?string
    {
        return null;
    }

    public function span(): WidgetSpan
    {
        return WidgetSpan::Half;
    }

    /** Render without the card shell (the KPI row). */
    public function flat(): bool
    {
        return false;
    }

    public function permission(): ?string
    {
        return null;
    }

    public function position(): Position
    {
        return Position::priority(50);
    }

    public function visibleByDefault(): bool
    {
        return true;
    }

    public function visible(?Authenticatable $user): bool
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
            'component' => $this->component(),
            'label' => $this->label(),
            'description' => $this->description(),
            'icon' => $this->icon(),
            'span' => $this->span()->value,
            'flat' => $this->flat(),
        ];
    }
}
