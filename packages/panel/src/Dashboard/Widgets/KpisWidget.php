<?php

namespace Lunar\Panel\Dashboard\Widgets;

use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\OrderMetrics;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Dashboard\WidgetSpan;
use Lunar\Panel\Support\Position;

class KpisWidget extends Widget
{
    public function __construct(protected OrderMetrics $metrics) {}

    public function key(): string
    {
        return 'kpis';
    }

    public function component(): string
    {
        return 'KpisWidget';
    }

    public function label(): string
    {
        return __('panel::dashboard.widget_kpis_label');
    }

    public function description(): ?string
    {
        return __('panel::dashboard.widget_kpis_description');
    }

    public function icon(): ?string
    {
        return 'chart';
    }

    public function span(): WidgetSpan
    {
        return WidgetSpan::Full;
    }

    public function flat(): bool
    {
        return true;
    }

    public function permission(): ?string
    {
        return 'sales:manage-orders';
    }

    public function position(): Position
    {
        return Position::priority(10);
    }

    public function data(DashboardRange $range): array
    {
        $current = $this->metrics->totals($range->start(), $range->end());
        $previous = $this->metrics->totals($range->previousStart(), $range->previousEnd());
        $series = $this->metrics->series($range);

        $aov = $current->orders > 0 ? (int) round($current->revenue / $current->orders) : 0;
        $previousAov = $previous->orders > 0 ? (int) round($previous->revenue / $previous->orders) : 0;

        $revenueSpark = array_map(fn (int $minor) => $this->metrics->major($minor), $series['revenue']);
        $orderSpark = $series['orders'];

        return [
            'tiles' => [
                [
                    'label' => __('panel::dashboard.kpi_revenue'),
                    'value' => $this->metrics->formatCompact($current->revenue),
                    'valueExact' => $this->metrics->format($current->revenue),
                    'icon' => 'chart',
                    'tone' => 'sage',
                    'delta' => $this->delta($current->revenue, $previous->revenue),
                    'spark' => $revenueSpark,
                ],
                [
                    'label' => __('panel::dashboard.kpi_orders'),
                    'value' => (string) $current->orders,
                    'icon' => 'cart',
                    'tone' => 'neutral',
                    'delta' => $this->delta($current->orders, $previous->orders),
                    'spark' => $orderSpark,
                ],
                [
                    'label' => __('panel::dashboard.kpi_avg_order'),
                    'value' => $this->metrics->formatCompact($aov),
                    'valueExact' => $this->metrics->format($aov),
                    'icon' => 'tag',
                    'tone' => 'neutral',
                    'delta' => $this->delta($aov, $previousAov),
                    'spark' => array_map(
                        fn (float $bucketRevenue, int $bucketOrders) => $bucketOrders > 0 ? $bucketRevenue / $bucketOrders : 0,
                        $revenueSpark,
                        $orderSpark,
                    ),
                ],
                [
                    'label' => __('panel::dashboard.kpi_new_customers'),
                    'value' => (string) $current->newOrders,
                    'icon' => 'users',
                    'tone' => 'warn',
                    'delta' => $this->delta($current->newOrders, $previous->newOrders),
                    'spark' => $series['newOrders'],
                ],
            ],
        ];
    }

    /** @return array{value: string, tone: string}|null */
    protected function delta(float $current, float $previous): ?array
    {
        if ($previous == 0.0 && $current == 0.0) {
            return null;
        }

        if ($previous == 0.0) {
            return ['value' => __('panel::dashboard.delta_new'), 'tone' => 'sage'];
        }

        $percent = (($current - $previous) / abs($previous)) * 100;
        $rounded = round($percent);

        return [
            'value' => sprintf('%s%d%%', $rounded >= 0 ? '+' : '', $rounded),
            'tone' => $rounded > 0 ? 'sage' : ($rounded < 0 ? 'danger' : 'neutral'),
        ];
    }
}
