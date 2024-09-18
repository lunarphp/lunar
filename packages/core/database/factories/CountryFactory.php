<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Country;

class CountryFactory extends BaseFactory
{
    protected $model = Country::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->country,
            'iso3' => $this->faker->unique()->regexify('[A-Z]{3}'),
            'iso2' => $this->faker->unique()->regexify('[A-Z]{2}'),
            'phonecode' => '+44',
            'capital' => $this->faker->city,
            'currency' => $this->faker->currencyCode,
            'native' => $this->faker->languageCode,
            'emoji' => $this->faker->emoji,
            'emoji_u' => $this->faker->emoji,
        ];
    }
}
