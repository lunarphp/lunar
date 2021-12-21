<?php

namespace GetCandy\Hub\Http\Livewire;

use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $categories = [];

        for ($i = 0; $i < 10; $i++) {
            $categories[] = Carbon::now()->addDays($i)->toDateTimeString();
        }

        // Sales Performance
        $options1 = collect([
            'chart' => [
                'type' => 'area',
                'toolbar' => [
                    'show' => false,
                ],
                'height' => '100%',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.45,
                    'opacityTo' => 0.05,
                    'stops' => [50, 100, 100, 100],
                ],
            ],
            'series' => [
                [
                    'name' => 'This Period',
                    'data' => [3000, 4000, 3500, 5000, 4900, 6000, 7000, 9100, 12500, 6300],
                ],
                [
                    'name' => 'Previous Period',
                    'data' => [2000, 3000, 4500, 4000, 2900, 7000, 8000, 4100, 9500, 9400],
                ],
            ],
            'xaxis' => [
                'type' => 'datetime',
                'categories' => $categories,
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Turnover $ USD',
                ],
            ],
            'tooltip' => [
                'x' => [
                    'format' => 'dd MMM yyyy',
                ],
            ],
        ]);

        // Customer Group Orders
        $options2 = collect([
            'chart' => [
                'type' => 'donut',
                'toolbar' => [
                    'show' => false,
                ],
                'height' => '100%',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'series' => [104, 55, 13, 33],
            'labels' => ['Guest', 'Retail', 'Trade', 'Distributor'],
            'legend' => [
                'position' => 'bottom',
            ],
        ]);

        return view('adminhub::livewire.dashboard')
            ->with('options1', $options1)
            ->with('options2', $options2);
    }
}
