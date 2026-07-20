<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\States\Collection\Archived;
use Lunar\Core\States\Collection\Draft;
use Lunar\Core\States\Collection\Published;

class CollectionFactory extends BaseFactory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        // Explicit handle: the model's creating hook is swallowed under
        // Event::fake(), and distinct names can slug identically — a unique
        // suffix keeps factory collections collision-free.
        return [
            'public_id' => (string) Str::ulid(),
            'collection_group_id' => CollectionGroup::factory(),
            'handle' => Str::slug($name.' '.$this->faker->unique()->numberBetween(1, 9999999)),
            'name' => collect(['en' => $name]),
            'description' => collect(['en' => $this->faker->paragraph]),
            'short_description' => collect(['en' => $this->faker->sentence]),
            'attribute_data' => collect(),
        ];
    }

    public function published(): self
    {
        return $this->state(['status' => Published::$name]);
    }

    public function draft(): self
    {
        return $this->state(['status' => Draft::$name]);
    }

    public function archived(): self
    {
        return $this->state(['status' => Archived::$name]);
    }
}
