<?php

namespace Tests\Feature\Api;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApplicationSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_applicant_can_apply_to_published_job(): void
    {
        $employer = User::factory()->employer()->create();
        $applicant = User::factory()->applicant()->create();

        $job = JobListing::factory()->published()->create(['user_id' => $employer->id]);

        $applicationData = [
            'message' => str_repeat('I am very interested in this position because I have relevant experience. ', 5),
        ];

        $response = $this->actingAs($applicant, 'sanctum')
            ->postJson("/api/jobs/{$job->id}/applications", $applicationData);
        dd($response);
        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'message',
                    'status',
                    'viewed_at',
                    'created_at',
                    'job' => ['id', 'title', 'company', 'status'],
                ],
            ])
            ->assertJsonPath('data.status', 'applied')
            ->assertJsonPath('data.viewed_at', null)
            ->assertJsonPath('data.job.id', $job->id);

        $this->assertDatabaseHas('applications', [
            'job_listing_id' => $job->id,
            'user_id' => $applicant->id,
            'status' => 'applied',
        ]);
    }

    public function test_applicant_cannot_apply_to_draft_job(): void
    {
        $employer = User::factory()->employer()->create();
        $applicant = User::factory()->applicant()->create();

        $job = JobListing::factory()->draft()->create(['user_id' => $employer->id]);

        $applicationData = [
            'message' => str_repeat('I want to apply. ', 10),
        ];

        $response = $this->actingAs($applicant, 'sanctum')
            ->postJson("/api/jobs/{$job->id}/applications", $applicationData);

        $response->assertNotFound();
    }

    public function test_applicant_cannot_apply_to_closed_job(): void
    {
        $employer = User::factory()->employer()->create();
        $applicant = User::factory()->applicant()->create();

        $job = JobListing::factory()->closed()->create(['user_id' => $employer->id]);

        $applicationData = [
            'message' => str_repeat('I want to apply. ', 10),
        ];

        $response = $this->actingAs($applicant, 'sanctum')
            ->postJson("/api/jobs/{$job->id}/applications", $applicationData);

        $response->assertNotFound();
    }

    public function test_applicant_cannot_apply_twice_to_same_job(): void
    {
        $employer = User::factory()->employer()->create();
        $applicant = User::factory()->applicant()->create();

        $job = JobListing::factory()->published()->create(['user_id' => $employer->id]);

        $job->applications()->create([
            'user_id' => $applicant->id,
            'message' => str_repeat('First application. ', 10),
        ]);

        $applicationData = [
            'message' => str_repeat('Second application attempt. ', 10),
        ];

        $response = $this->actingAs($applicant, 'sanctum')
            ->postJson("/api/jobs/{$job->id}/applications", $applicationData);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'You have already applied to this job',
            ]);

        $this->assertEquals(1, Application::where('job_listing_id', $job->id)
            ->where('user_id', $applicant->id)
            ->count());
    }

    public function test_employer_cannot_apply_to_jobs(): void
    {
        $employer = User::factory()->employer()->create();
        $otherEmployer = User::factory()->employer()->create();

        $job = JobListing::factory()->published()->create(['user_id' => $otherEmployer->id]);

        $applicationData = [
            'message' => str_repeat('I want to apply. ', 10),
        ];

        $response = $this->actingAs($employer, 'sanctum')
            ->postJson("/api/jobs/{$job->id}/applications", $applicationData);

        $response->assertForbidden();
    }

    public function test_guest_cannot_apply_to_jobs(): void
    {
        $employer = User::factory()->employer()->create();
        $job = JobListing::factory()->published()->create(['user_id' => $employer->id]);

        $applicationData = [
            'message' => str_repeat('I want to apply. ', 10),
        ];

        $response = $this->postJson("/api/jobs/{$job->id}/applications", $applicationData);

        $response->assertUnauthorized();
    }

    public function test_applicant_can_view_their_applications(): void
    {
        $employer = User::factory()->employer()->create();
        $applicant = User::factory()->applicant()->create();

        // Create 3 jobs and apply to them
        $jobs = JobListing::factory()->published()->count(3)->create(['user_id' => $employer->id]);

        foreach ($jobs as $job) {
            $job->applications()->create([
                'user_id' => $applicant->id,
                'message' => str_repeat("Application for {$job->title}. ", 10),
            ]);
        }

        $response = $this->actingAs($applicant, 'sanctum')
            ->getJson('/api/my-applications');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'message',
                        'status',
                        'viewed_at',
                        'created_at',
                        'job' => ['id', 'title', 'company', 'status'],
                    ],
                ],
            ]);
    }

    public function test_employer_cannot_access_my_applications_endpoint(): void
    {
        $employer = User::factory()->employer()->create();

        $response = $this->actingAs($employer, 'sanctum')
            ->getJson('/api/my-applications');

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_my_applications_endpoint(): void
    {
        $response = $this->getJson('/api/my-applications');

        $response->assertUnauthorized();
    }
}
