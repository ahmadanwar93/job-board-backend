<?php

namespace Tests\Feature\Api;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmployerJobManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_can_view_their_own_jobs_including_drafts(): void
    {
        $employer = User::factory()->employer()->create();

        $publishedJob = JobListing::factory()->published()->create(['user_id' => $employer->id]);
        $draftJob = JobListing::factory()->draft()->create(['user_id' => $employer->id]);
        $closedJob = JobListing::factory()->closed()->create(['user_id' => $employer->id]);

        $otherEmployer = User::factory()->employer()->create();
        JobListing::factory()->published()->create(['user_id' => $otherEmployer->id]);

        $response = $this->actingAs($employer, 'sanctum')
            ->getJson('/api/employer/jobs');

        $response->assertOk()
            ->assertJsonCount(3, 'data');

        $jobIds = collect($response->json('data'))->pluck('id')->toArray();

        $this->assertContains($publishedJob->id, $jobIds);
        $this->assertContains($draftJob->id, $jobIds);
        $this->assertContains($closedJob->id, $jobIds);
    }

    public function test_employer_can_filter_jobs_by_status(): void
    {
        $employer = User::factory()->employer()->create();

        JobListing::factory()->published()->count(3)->create(['user_id' => $employer->id]);
        JobListing::factory()->draft()->count(2)->create(['user_id' => $employer->id]);
        JobListing::factory()->closed()->count(1)->create(['user_id' => $employer->id]);

        $response = $this->actingAs($employer, 'sanctum')
            ->getJson('/api/employer/jobs?status=published');

        $response->assertOk()
            ->assertJsonCount(3, 'data');

        $statuses = collect($response->json('data'))->pluck('status')->unique()->toArray();
        $this->assertEquals(['published'], $statuses);
    }

    public function test_applicant_cannot_access_employer_job_listing_endpoint(): void
    {
        $applicant = User::factory()->applicant()->create();

        $response = $this->actingAs($applicant, 'sanctum')
            ->getJson('/api/employer/jobs');
        // error at policy level
        $response->assertForbidden();
    }

    public function test_guest_cannot_access_employer_job_listing_endpoint(): void
    {
        $response = $this->getJson('/api/employer/jobs');
        // this is because sanctum caught the error (middleware layer)
        $response->assertUnauthorized();
    }

    public function test_employer_can_create_draft_job(): void
    {
        $employer = User::factory()->employer()->create();

        $jobData = [
            'title' => 'Senior Laravel Developer',
            'description' => str_repeat('We are looking for an experienced Laravel developer. ', 10),
            'location' => 'Kuala Lumpur',
            'salary_range' => '8k-12k MYR',
            'is_remote' => true,
            'status' => 'draft',
        ];

        $response = $this->actingAs($employer, 'sanctum')
            ->postJson('/api/employer/jobs', $jobData);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Senior Laravel Developer')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.employer.id', $employer->id);

        $this->assertDatabaseHas('job_listings', [
            'title' => 'Senior Laravel Developer',
            'status' => 'draft',
            'user_id' => $employer->id,
        ]);
    }

    public function test_employer_can_create_published_job(): void
    {
        $employer = User::factory()->employer()->create();

        $jobData = [
            'title' => 'Frontend Developer',
            'description' => str_repeat('Join our amazing team building the future.', 10),
            'location' => 'Remote',
            'salary_range' => '6k-9k MYR',
            'is_remote' => true,
            'status' => 'published',
        ];

        $response = $this->actingAs($employer, 'sanctum')
            ->postJson('/api/employer/jobs', $jobData);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'published');

        $this->assertDatabaseHas('job_listings', [
            'title' => 'Frontend Developer',
            'description' => str_repeat('Join our amazing team building the future.', 10),
            'location' => 'Remote',
            'status' => 'published',
        ]);
    }

    public function test_applicant_cannot_create_job(): void
    {
        $applicant = User::factory()->applicant()->create();

        $jobData = [
            'title' => 'Test Job',
            'description' => str_repeat('Description. ', 10),
            'location' => 'KL',
            'salary_range' => '5k-8k',
            'is_remote' => false,
            'status' => 'draft',
        ];

        $response = $this->actingAs($applicant, 'sanctum')
            ->postJson('/api/employer/jobs', $jobData);

        $response->assertForbidden();
    }

    public function test_employer_can_view_their_own_job_details(): void
    {
        $employer = User::factory()->employer()->create();
        $job = JobListing::factory()->draft()->create([
            'user_id' => $employer->id,
            'title' => 'My Draft Job',
        ]);

        $response = $this->actingAs($employer, 'sanctum')
            ->getJson("/api/employer/jobs/{$job->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $job->id)
            ->assertJsonPath('data.title', 'My Draft Job')
            ->assertJsonPath('data.status', 'draft');
    }

    public function test_employer_cannot_view_other_employers_job(): void
    {
        $employer1 = User::factory()->employer()->create();
        $employer2 = User::factory()->employer()->create();

        $job = JobListing::factory()->published()->create(['user_id' => $employer2->id]);

        $response = $this->actingAs($employer1, 'sanctum')
            ->getJson("/api/employer/jobs/{$job->id}");

        $response->assertForbidden();
    }

    public function test_employer_can_update_their_own_job(): void
    {
        $employer = User::factory()->employer()->create();
        $job = JobListing::factory()->draft()->create([
            'user_id' => $employer->id,
            'title' => 'Old Title',
            'status' => 'draft',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => str_repeat('Updated description. ', 10),
            'location' => 'Penang',
            'salary_range' => '10k-15k MYR',
            'is_remote' => false,
            'status' => 'published',
        ];

        $response = $this->actingAs($employer, 'sanctum')
            ->putJson("/api/employer/jobs/{$job->id}", $updateData);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.location', 'Penang');

        $this->assertDatabaseHas('job_listings', [
            'id' => $job->id,
            'title' => 'Updated Title',
            'status' => 'published',
        ]);
    }

    public function test_employer_cannot_update_other_employers_job(): void
    {
        $employer1 = User::factory()->employer()->create();
        $employer2 = User::factory()->employer()->create();

        $job = JobListing::factory()->published()->create(['user_id' => $employer2->id]);

        $updateData = [
            'title' => 'Malicious Update',
            'description' => str_repeat('Hacked data. ', 10),
            'location' => 'KL',
            'salary_range' => '1k',
            'is_remote' => false,
            'status' => 'closed',
        ];

        $response = $this->actingAs($employer1, 'sanctum')
            ->putJson("/api/employer/jobs/{$job->id}", $updateData);

        $response->assertForbidden();

        $this->assertDatabaseMissing('job_listings', [
            'id' => $job->id,
            'title' => 'Malicious Update',
        ]);
    }

    public function test_employer_can_delete_their_own_job(): void
    {
        $employer = User::factory()->employer()->create();
        $job = JobListing::factory()->draft()->create(['user_id' => $employer->id]);

        $response = $this->actingAs($employer, 'sanctum')
            ->deleteJson("/api/employer/jobs/{$job->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Job deleted successfully']);

        $this->assertDatabaseMissing('job_listings', [
            'id' => $job->id,
        ]);
    }

    public function test_employer_cannot_delete_other_employers_job(): void
    {
        $employer1 = User::factory()->employer()->create();
        $employer2 = User::factory()->employer()->create();

        $job = JobListing::factory()->published()->create(['user_id' => $employer2->id]);

        $response = $this->actingAs($employer1, 'sanctum')
            ->deleteJson("/api/employer/jobs/{$job->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('job_listings', [
            'id' => $job->id,
        ]);
    }
}
