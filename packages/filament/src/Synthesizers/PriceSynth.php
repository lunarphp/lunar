<?php

namespace Lunar\Filament\Synthesizers;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Currency;

class PriceSynth extends Synth
{
    public static $key = 'lunar_price';

    public static function match($target)
    {
        return $target instanceof PriceValue;
    }

    public function dehydrate($target)
    {
        return [[
            'value' => $target->value,
            'currency' => $target->resolveCurrency()->code,
        ], []];
    }

    public function hydrate($value)
    {
        $currency = Currency::where('code', $value['currency'])->first();

        return new PriceValue($value['value'], $currency);
    }
}
