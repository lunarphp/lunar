<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Region;

class RegionFactory extends BaseFactory
{
    protected $model = Region::class;

    public function definition(): array
    {
        $name = $this->faker->country();

        return [
            'public_id' => (string) Str::ulid(),
            'name' => $name,
            'handle' => Str::slug($name).'-'.Str::random(4),
            'channel_id' => Channel::factory(),
            'currency_id' => Currency::factory(),
            'language_id' => Language::factory(),
            'tax_zone_id' => null,
            'prices_inc_tax' => null,
            'default' => true,
        ];
    }
}
