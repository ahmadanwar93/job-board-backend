<?php

namespace App\Http\Controllers\Api;

use App\Enums\JobListingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobListingRequest;
use App\Http\Requests\UpdateJobListingRequest;
use App\Http\Resources\JobListingResource;
use App\Models\JobListing;
use App\Policies\JobListingPolicy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployerJobListingController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        // has to pass in the second argument because of name convention
        $this->authorize('viewAny', JobListing::class);

        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'status' => ['sometimes', 'string', Rule::in(JobListingStatus::values())],
        ]);

        $perPage = $validated['per_page'] ?? 5;

        $query = JobListing::query()
            ->with('user')
            ->withCount('applications')
            ->forEmployer($request->user()->id);

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $jobListings = $query->latest()->paginate($perPage);

        return JobListingResource::collection($jobListings);
    }

    public function store(StoreJobListingRequest $request)
    {
        $this->authorize('create', JobListing::class);
        $jobListing = JobListing::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        $jobListing->load('user');

        return response()->json([
            'data' => new JobListingResource($jobListing)
        ], 201);
    }

    public function show(JobListing $jobListing)
    {
        $this->authorize('view', $jobListing);
        $jobListing->load('user');
        $jobListing->loadCount('applications');

        return new JobListingResource($jobListing);
    }

    public function update(UpdateJobListingRequest $request, JobListing $jobListing)
    {
        $this->authorize('update', $jobListing);
        $jobListing->update($request->validated());
        $jobListing->load('user');

        return new JobListingResource($jobListing);
    }

    public function destroy(JobListing $jobListing)
    {
        $this->authorize('delete', $jobListing);
        $jobListing->delete();

        return response()->json([
            'message' => 'Job deleted successfully'
        ]);
    }
}
