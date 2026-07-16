<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Channel;

class ChannelFactory extends BaseFactory
{
    protected $model = Channel::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'name' => $this->faker->name(),
            'handle' => Str::slug($this->faker->name()),
            'default' => true,
            'url' => $this->faker->url(),
        ];
    }
}
