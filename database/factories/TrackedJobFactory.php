<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Junges\TrackableJobs\Models\TrackedJob;

class TrackedJobFactory extends Factory
{
    protected $model = TrackedJob::class;

    public function definition(): array
    {
        return [
            'trackable_id' => $this->faker->randomDigit(),
            'trackable_type' => $this->faker->userName,
            'name' => $this->faker->name,
            'status' => TrackedJob::STATUSES[rand(0, count(TrackedJob::STATUSES) - 1)],
            'output' => $this->faker->text(250),
            'started_at' => now(),
            'finished_at' => now()->addHour(),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}