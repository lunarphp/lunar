<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Lunar\Models\Channel;

class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    public function definition(): array
    {
        return [
            'name'    => $this->faker->name(),
            'handle'  => Str::slug($this->faker->name()),
            'default' => true,
            'url'     => $this->faker->url(),
        ];
    }
}
