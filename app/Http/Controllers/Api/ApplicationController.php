<?php

namespace App\Http\Controllers\Api;

use App\Enums\JobListingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\JobListing;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    use AuthorizesRequests;
    public function store(StoreApplicationRequest $request, JobListing $jobListing)
    {
        $this->authorize('create', Application::class);

        if ($jobListing->status !== JobListingStatus::PUBLISHED) {
            return response()->json([
                'message' => 'Job not found or not available'
            ], 404);
        }

        if ($jobListing->applications()->where('user_id', $request->user()->id)->exists()) {
            return response()->json([
                'message' => 'Already applied to this job'
            ], 409);
        }

        $application = Application::create([
            'job_listing_id' => $jobListing->id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
        ]);

        // TODO: double check frontend if needed this or not. I am thinking to just always load
        $application->load(['jobListing.user']);

        return response()->json([
            'data' => new ApplicationResource($application)
        ], 201);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Application::class);

        $applications = Application::with(['jobListing.user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return ApplicationResource::collection($applications);
    }

    public function forJob(Request $request, JobListing $jobListing)
    {
        $this->authorize('viewApplications', $jobListing);

        $applications = $jobListing->applications()
            ->with('user')
            ->latest()
            ->get();

        return ApplicationResource::collection($applications);
    }
}
