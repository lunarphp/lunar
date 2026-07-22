<?php

namespace Lunar\Panel\Dashboard\Widgets;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Customer;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\OrderMetrics;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Support\Position;

class CustomerGroupsWidget extends Widget
{
    public function __construct(protected OrderMetrics $metrics) {}

    public function key(): string
    {
        return 'customer-groups';
    }

    public function component(): string
    {
        return 'CustomerGroupsWidget';
    }

    public function label(): string
    {
        return __('panel::dashboard.widget_customer_groups_label');
    }

    public function description(): ?string
    {
        return __('panel::dashboard.widget_customer_groups_description');
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
        return Position::priority(70);
    }

    public function visibleByDefault(): bool
    {
        return false;
    }

    public function data(DashboardRange $range): array
    {
        $revenueByCustomer = $this->metrics
            ->revenueByColumn($range->start(), $range->end(), 'customer_id');

        // Revenue attributes to the customer's first group; guests and
        // ungrouped customers pool into a single remainder bucket.
        $groupByCustomer = Customer::query()
            ->whereIn('id', $revenueByCustomer->keys()->filter())
            ->with('customerGroups:id,name')
            ->get(['id'])
            ->mapWithKeys(fn (Customer $customer) => [
                $customer->id => $customer->customerGroups->first()?->name,
            ]);

        $totals = $revenueByCustomer
            ->groupBy(fn (int $minor, $customerId) => $groupByCustomer[$customerId] ?? '')
            ->map(fn (Collection $group) => (int) $group->sum())
            ->sortDesc();

        $segments = $totals
            ->map(fn (int $minor, string $name) => [
                'label' => $name !== '' ? $name : __('panel::dashboard.segment_other'),
                'value' => $this->metrics->major($minor),
                'display' => $this->metrics->format($minor),
            ])
            ->values()
            ->all();

        $total = (int) $totals->sum();

        return [
            'segments' => $segments,
            'total' => $this->metrics->formatCompact($total),
            'totalExact' => $this->metrics->format($total),
        ];
    }
}
