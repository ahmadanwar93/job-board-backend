<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobListingRequest;
use App\Http\Requests\UpdateJobListingRequest;
use App\Http\Resources\JobListingResource;
use App\Models\JobListing;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class EmployerJobListingController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('viewAny');

        $jobListings = JobListing::with('user')
            ->withCount('applications')
            ->forEmployer($request->user()->id)
            ->latest()
            ->get();

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
        $this->authorize('show', JobListing::class);
        $jobListing->load('user');
        $jobListing->loadCount('applications');

        return new JobListingResource($jobListing);
    }

    public function update(UpdateJobListingRequest $request, JobListing $jobListing)
    {
        $this->authorize('update', JobListing::class);
        $jobListing->update($request->validated());
        $jobListing->load('user');

        return new JobListingResource($jobListing);
    }

    public function destroy(JobListing $jobListing)
    {
        $this->authorize('delete', JobListing::class);
        $jobListing->delete();

        return response()->json([
            'message' => 'Job deleted successfully'
        ]);
    }
}
