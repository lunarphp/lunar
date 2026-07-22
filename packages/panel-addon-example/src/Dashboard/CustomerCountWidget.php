<?php

namespace LunarPanelExample\Dashboard;

use Lunar\Core\Models\Customer;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Support\Position;

/**
 * A minimal dashboard widget: identified, permission-gated, hidden by
 * default so it appears in the Customise dialog. The panel owns the card
 * chrome; the component named here renders only the body, receiving the
 * data() payload as its `data` prop.
 */
class CustomerCountWidget extends Widget
{
    public function key(): string
    {
        return 'example-addon-customers';
    }

    public function component(): string
    {
        return 'example-addon::CustomerCountWidget';
    }

    public function label(): string
    {
        return __('example-addon::example.widget_label');
    }

    public function description(): ?string
    {
        return __('example-addon::example.widget_description');
    }

    public function icon(): ?string
    {
        return 'users';
    }

    public function permission(): ?string
    {
        return 'sales:manage-customers';
    }

    public function position(): Position
    {
        return Position::last();
    }

    public function visibleByDefault(): bool
    {
        return false;
    }

    public function data(DashboardRange $range): array
    {
        return [
            'total' => Customer::query()->count(),
            'recent' => Customer::query()->where('created_at', '>=', $range->start())->count(),
        ];
    }
}
