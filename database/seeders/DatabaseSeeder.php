<?php

namespace Database\Seeders;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $employer = User::factory()->employer()->create();

        JobListing::factory()->published()->count(10)->create(['user_id' => $employer->id]);
        JobListing::factory()->draft()->count(10)->create(['user_id' => $employer->id]);
        JobListing::factory()->closed()->count(10)->create(['user_id' => $employer->id]);
    }
}
