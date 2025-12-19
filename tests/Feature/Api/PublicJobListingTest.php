<?php

namespace Tests\Feature\Api;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PublicJobListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_published_jobs(): void
    {
        $employer = User::factory()->employer()->create();

        JobListing::factory()->published()->count(3)->create(['user_id' => $employer->id]);
        JobListing::factory()->draft()->count(2)->create(['user_id' => $employer->id]);
        JobListing::factory()->closed()->count(1)->create(['user_id' => $employer->id]);

        $response = $this->getJson('/api/jobs');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'location',
                        'salary_range',
                        'is_remote',
                        'status',
                        'employer' => ['id', 'name'],
                        'created_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_guest_only_sees_published_jobs_not_drafts_or_closed(): void
    {
        $employer = User::factory()->employer()->create();

        JobListing::factory()->published()->create([
            'user_id' => $employer->id,
            'title' => 'Published Job',
        ]);

        JobListing::factory()->draft()->create([
            'user_id' => $employer->id,
            'title' => 'Draft Job',
        ]);

        JobListing::factory()->closed()->create([
            'user_id' => $employer->id,
            'title' => 'Closed Job',
        ]);

        $response = $this->getJson('/api/jobs');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Published Job');
    }

    public function test_job_listing_includes_employer_information(): void
    {
        $employer = User::factory()->employer()->create([
            'name' => 'Tech Corp Malaysia',
        ]);

        JobListing::factory()->published()->create([
            'user_id' => $employer->id,
            'title' => 'Senior Laravel Developer',
        ]);

        $response = $this->getJson('/api/jobs');

        $response->assertOk()
            ->assertJsonPath('data.0.employer.name', 'Tech Corp Malaysia')
            ->assertJsonPath('data.0.title', 'Senior Laravel Developer')
            ->assertJsonPath('data.0.employer.id', $employer->id);
    }

    public function test_authenticated_applicant_sees_has_applied_flag(): void
    {
        $employer = User::factory()->employer()->create();
        $applicant = User::factory()->applicant()->create();
        $jobApplied = JobListing::factory()->published()->create(['user_id' => $employer->id]);
        $jobNotApplied = JobListing::factory()->published()->create(['user_id' => $employer->id]);

        $jobApplied->applications()->create([
            'user_id' => $applicant->id,
            'message' => 'I am interested in this position.',
        ]);

        $response = $this->actingAs($applicant, 'sanctum')
            ->getJson('/api/jobs');
        $response->assertOk();

        // extract the jobs in the resource
        $jobs = collect($response->json('data'));

        $appliedJob = $jobs->firstWhere('id', $jobApplied->id);
        $notAppliedJob = $jobs->firstWhere('id', $jobNotApplied->id);

        $this->assertTrue($appliedJob['has_applied']);
        $this->assertFalse($notAppliedJob['has_applied']);
    }

    public function test_guest_does_not_see_has_applied_flag(): void
    {
        $employer = User::factory()->employer()->create();
        JobListing::factory()->published()->create(['user_id' => $employer->id]);

        $response = $this->getJson('/api/jobs');

        $response->assertOk();

        $job = $response->json('data.0');
        $this->assertArrayNotHasKey('has_applied', $job);
    }

    public function test_authenticated_applicant_sees_their_application_on_single_job(): void
    {
        $employer = User::factory()->employer()->create();
        $applicant = User::factory()->applicant()->create();

        $job = JobListing::factory()->published()->create(['user_id' => $employer->id]);

        $application = $job->applications()->create([
            'user_id' => $applicant->id,
            'message' => 'My application message',
        ]);

        $response = $this->actingAs($applicant, 'sanctum')
            ->getJson("/api/jobs/{$job->id}");

        $response->assertOk()
            ->assertJsonPath('data.has_applied', true)
            ->assertJsonPath('data.my_application.message', 'My application message')
            ->assertJsonPath('data.my_application.viewed_at', null)
            ->assertJsonPath('data.my_application.status', 'pending');
    }

    public function test_viewing_nonexistent_job_returns_404(): void
    {
        $response = $this->getJson('/api/jobs/99999');

        $response->assertNotFound();
    }
}
