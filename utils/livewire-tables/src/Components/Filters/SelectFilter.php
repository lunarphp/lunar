<?php

namespace Lunar\LivewireTables\Components\Filters;

use Closure;

class SelectFilter extends BaseFilter
{
    public $options;

    public $heading;

    public $view = 'l-tables::filters.select';

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
