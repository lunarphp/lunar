<?php

namespace Lunar\Panel\Dashboard\Widgets;

use Lunar\Core\Models\Channel;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\OrderMetrics;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Support\Position;

class ChannelsWidget extends Widget
{
    public function __construct(protected OrderMetrics $metrics) {}

    public function key(): string
    {
        return 'channels';
    }

    public function component(): string
    {
        return 'ChannelsWidget';
    }

    public function label(): string
    {
        return __('panel::dashboard.widget_channels_label');
    }

    public function description(): ?string
    {
        return __('panel::dashboard.widget_channels_description');
    }

    public function icon(): ?string
    {
        return 'globe';
    }

    public function permission(): ?string
    {
        return 'sales:manage-orders';
    }

    public function position(): Position
    {
        return Position::priority(50);
    }

    public function data(DashboardRange $range): array
    {
        $names = Channel::query()->pluck('name', 'id');

        $totals = $this->metrics
            ->revenueByColumn($range->start(), $range->end(), 'channel_id')
            ->sortDesc();

        $segments = $totals
            ->map(fn (int $minor, $channelId) => [
                'label' => $names[$channelId] ?? __('panel::dashboard.segment_other'),
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
