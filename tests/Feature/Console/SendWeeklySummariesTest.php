<?php

namespace Tests\Feature\Console;

use App\Models\JobListing;
use App\Models\User;
use App\Notifications\WeeklyApplicantSummary;
use App\Notifications\WeeklyEmployerSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendWeeklySummariesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_summary_to_applicants_with_recent_activity(): void
    {
        Notification::fake();

        $employer = User::factory()->employer()->create();
        $applicant = User::factory()->applicant()->create();

        $job = JobListing::factory()->published()->create(['user_id' => $employer->id]);

        $job->applications()->create([
            'user_id' => $applicant->id,
            'message' => str_repeat('Application. ', 10),
            'created_at' => now()->subDays(3),
        ]);

        Artisan::call('app:send-weekly-summaries');

        Notification::assertSentTo($applicant, WeeklyApplicantSummary::class);
    }

    public function test_does_not_send_summary_to_applicants_without_recent_activity(): void
    {
        Notification::fake();

        $employer = User::factory()->employer()->create();
        $applicant = User::factory()->applicant()->create();

        $job = JobListing::factory()->published()->create(['user_id' => $employer->id]);

        $job->applications()->create([
            'user_id' => $applicant->id,
            'message' => str_repeat('Application. ', 10),
            'created_at' => now()->subDays(10),
        ]);

        Artisan::call('app:send-weekly-summaries');

        Notification::assertNotSentTo($applicant, WeeklyApplicantSummary::class);
    }

    public function test_sends_summary_to_employers_with_recent_activity(): void
    {
        Notification::fake();

        $employer = User::factory()->employer()->create();
        $applicant = User::factory()->applicant()->create();

        // Job posted within the last week
        $job = JobListing::factory()->published()->create([
            'user_id' => $employer->id,
            'created_at' => now()->subDays(4),
        ]);

        Artisan::call('app:send-weekly-summaries');

        Notification::assertSentTo($employer, WeeklyEmployerSummary::class);
    }

    public function test_does_not_send_summary_to_employers_without_recent_activity(): void
    {
        Notification::fake();

        $employer = User::factory()->employer()->create();

        // Job posted more than a week ago
        JobListing::factory()->published()->create([
            'user_id' => $employer->id,
            'created_at' => now()->subDays(10),
        ]);

        Artisan::call('app:send-weekly-summaries');

        Notification::assertNotSentTo($employer, WeeklyEmployerSummary::class);
    }
}
