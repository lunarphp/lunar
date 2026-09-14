<?php

namespace Lunar\SearchRelevance\Panel\Widgets;

use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Dashboard\WidgetSpan;
use Lunar\SearchRelevance\Panel\Reports\SearchKpis;
use Lunar\SearchRelevance\Panel\SearchRelevanceSection;

class SearchConversionWidget extends Widget
{
    public function __construct(protected SearchKpis $kpis) {}

    public function key(): string
    {
        return 'search-relevance-conversion';
    }

    public function component(): string
    {
        return 'search-relevance::SearchConversionWidget';
    }

    public function label(): string
    {
        return __('search-relevance::panel.widget_label');
    }

    public function description(): ?string
    {
        return __('search-relevance::panel.widget_description');
    }

    public function icon(): ?string
    {
        return 'search';
    }

    public function span(): WidgetSpan
    {
        return WidgetSpan::Half;
    }

    public function permission(): ?string
    {
        return SearchRelevanceSection::PERMISSION;
    }

    public function data(DashboardRange $range): array
    {
        $current = $this->kpis->forWindow($range->start(), $range->end());
        $previous = $this->kpis->forWindow($range->previousStart(), $range->previousEnd());

        return [
            'searches' => $current['searches'],
            'previous_searches' => $previous['searches'],
            'conversion_rate' => $current['conversion_rate'],
            'previous_conversion_rate' => $previous['conversion_rate'],
            'searches_delta' => $this->delta($current['searches'], $previous['searches']),
            'conversion_delta' => $this->delta($current['conversion_rate'], $previous['conversion_rate']),
            'url' => route('panel.search-relevance.index', ['range' => $range->value]),
        ];
    }

    /** @return array{value: string, tone: string}|null */
    protected function delta(float $current, float $previous): ?array
    {
        if ($previous == 0.0 && $current == 0.0) {
            return null;
        }

        if ($previous == 0.0) {
            return ['value' => __('search-relevance::panel.delta_new'), 'tone' => 'sage'];
        }

        $rounded = round(($current - $previous) / abs($previous) * 100);

        return [
            'value' => sprintf('%s%d%%', $rounded >= 0 ? '+' : '', $rounded),
            'tone' => $rounded > 0 ? 'sage' : ($rounded < 0 ? 'danger' : 'neutral'),
        ];
    }
}
