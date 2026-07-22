<?php

namespace Lunar\Panel\Dashboard\Widgets;

use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\OrderMetrics;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Dashboard\WidgetSpan;
use Lunar\Panel\Support\Position;

class RevenueChartWidget extends Widget
{
    public function __construct(protected OrderMetrics $metrics) {}

    public function key(): string
    {
        return 'revenue-chart';
    }

    public function component(): string
    {
        return 'RevenueChartWidget';
    }

    public function label(): string
    {
        return __('panel::dashboard.widget_revenue_chart_label');
    }

    public function description(): ?string
    {
        return __('panel::dashboard.widget_revenue_chart_description');
    }

    public function icon(): ?string
    {
        return 'chart';
    }

    public function span(): WidgetSpan
    {
        return WidgetSpan::Full;
    }

    public function permission(): ?string
    {
        return 'sales:manage-orders';
    }

    public function position(): Position
    {
        return Position::priority(20);
    }

    public function data(DashboardRange $range): array
    {
        $series = $this->metrics->series($range);

        $points = [];
        $totalMinor = 0;

        foreach ($range->buckets() as $index => $bucket) {
            $bucketMinor = $series['revenue'][$index];
            $totalMinor += $bucketMinor;

            $points[] = [
                'label' => $bucket['label'],
                'value' => $this->metrics->major($bucketMinor),
                'display' => $this->metrics->format($bucketMinor),
            ];
        }

        return [
            'points' => $points,
            'total' => $this->metrics->format($totalMinor),
            'hasOrders' => array_sum($series['orders']) > 0,
        ];
    }
}
