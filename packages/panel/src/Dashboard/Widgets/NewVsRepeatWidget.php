<?php

namespace Lunar\Panel\Dashboard\Widgets;

use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\OrderMetrics;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Support\Position;

class NewVsRepeatWidget extends Widget
{
    public function __construct(protected OrderMetrics $metrics) {}

    public function key(): string
    {
        return 'new-vs-repeat';
    }

    public function component(): string
    {
        return 'NewVsRepeatWidget';
    }

    public function label(): string
    {
        return __('panel::dashboard.widget_new_vs_repeat_label');
    }

    public function description(): ?string
    {
        return __('panel::dashboard.widget_new_vs_repeat_description');
    }

    public function icon(): ?string
    {
        return 'users';
    }

    public function permission(): ?string
    {
        return 'sales:manage-orders';
    }

    public function position(): Position
    {
        return Position::priority(60);
    }

    public function data(DashboardRange $range): array
    {
        $totals = $this->metrics->totals($range->start(), $range->end());

        return [
            'segments' => [
                [
                    'label' => __('panel::dashboard.segment_new_customers'),
                    'value' => $this->metrics->major($totals->newRevenue),
                    'display' => $this->metrics->format($totals->newRevenue),
                ],
                [
                    'label' => __('panel::dashboard.segment_returning_customers'),
                    'value' => $this->metrics->major($totals->repeatRevenue),
                    'display' => $this->metrics->format($totals->repeatRevenue),
                ],
            ],
            'counts' => [
                'new' => $totals->newOrders,
                'repeat' => $totals->repeatOrders,
            ],
            'total' => $this->metrics->formatCompact($totals->newRevenue + $totals->repeatRevenue),
            'totalExact' => $this->metrics->format($totals->newRevenue + $totals->repeatRevenue),
        ];
    }
}
