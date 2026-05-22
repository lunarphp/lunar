<?php

namespace Lunar\Filament\Widgets\Dashboard\Orders\Concerns;

trait HasChartPalette
{
    /**
     * Tailwind-flavoured palette cycled across datasets so multiple series in
     * the same chart pick up distinct colours.
     *
     * @return array<int, array{border: string, background: string}>
     */
    protected function chartPalette(): array
    {
        return [
            ['border' => 'rgb(59, 130, 246)', 'background' => 'rgba(59, 130, 246, 0.2)'],   // blue
            ['border' => 'rgb(34, 197, 94)',  'background' => 'rgba(34, 197, 94, 0.2)'],    // green
            ['border' => 'rgb(245, 158, 11)', 'background' => 'rgba(245, 158, 11, 0.2)'],   // amber
            ['border' => 'rgb(236, 72, 153)', 'background' => 'rgba(236, 72, 153, 0.2)'],   // pink
            ['border' => 'rgb(168, 85, 247)', 'background' => 'rgba(168, 85, 247, 0.2)'],   // purple
            ['border' => 'rgb(6, 182, 212)',  'background' => 'rgba(6, 182, 212, 0.2)'],    // cyan
            ['border' => 'rgb(244, 63, 94)',  'background' => 'rgba(244, 63, 94, 0.2)'],    // rose
            ['border' => 'rgb(132, 204, 22)', 'background' => 'rgba(132, 204, 22, 0.2)'],   // lime
        ];
    }

    /**
     * @return array{border: string, background: string}
     */
    protected function chartColor(int $index): array
    {
        $palette = $this->chartPalette();

        return $palette[$index % count($palette)];
    }
}
