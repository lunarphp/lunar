<?php

namespace Lunar\LivewireTables\Components\Filters;

use Closure;
use Livewire\Component;
use Lunar\LivewireTables\Components\Filters\BaseFilter;

class SelectFilter extends BaseFilter
{
    public $options;

    public $heading;

    public $view = 'lt::filters.select';

    public function mount()
    {
        $this->options = collect();
    }

    public function getViewData()
    {
        return [
            'options' => $this->options,
        ];
    }

    public function options($options)
    {
        if ($options instanceof Closure) {
            $options = call_user_func($options);
        }

        $this->options = $options->map(function ($option, $key) {
            if (! is_array($option)) {
                return [
                    'label' => $option,
                    'value' => $key,
                ];
            }

            return [
                'label' => $option['label'],
                'value' => $option['value'] ?? $option['label'],
            ];
        })->values();

        return $this;
    }
}
