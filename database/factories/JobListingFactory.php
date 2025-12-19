<?php

namespace Database\Factories;

use App\Enums\JobListingStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->employer(),
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraphs(5, true),
            'location' => fake()->city(),
            'salary_range' => fake()->randomElement([
                '3k-5k MYR',
                '5k-8k MYR',
                '8k-12k MYR',
                '12k-20k MYR',
            ]),
            'is_remote' => fake()->boolean(30),
            'status' => fake()->randomElement(JobListingStatus::values()),
        ];
    }

    public function published(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => JobListingStatus::PUBLISHED,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => JobListingStatus::DRAFT,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => JobListingStatus::CLOSED,
        ]);
    }
}
