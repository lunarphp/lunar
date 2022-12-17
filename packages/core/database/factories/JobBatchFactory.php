<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Lunar\Models\JobBatch;

class JobBatchFactory extends Factory
{
    protected $model = JobBatch::class;

    public function definition(): array
    {
        return [
            'id' => (string)Str::orderedUuid(),
            'name' => $this->faker->sentence,
            'total_jobs' => 0,
            'pending_jobs' => 0,
            'failed_jobs' => 0,
            'failed_job_ids' => '[]',
            'created_at' => time(),
            'cancelled_at' => null,
            'finished_at' => null,
        ];
    }
}
