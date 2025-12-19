<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_listing_id' => JobListing::factory()->published(),
            'user_id' => User::factory()->applicant(),
            'message' => fake()->paragraphs(3, true),
            'status' => 'applied'
        ];
    }
}
