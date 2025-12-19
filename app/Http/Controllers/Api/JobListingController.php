<?php

namespace App\Http\Controllers\Api;

use App\Enums\JobListingStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\JobListingResource;
use App\Models\JobListing;
use Illuminate\Http\Request;

class JobListingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);

        $jobListings = JobListing::with('user')
            ->published()
            ->latest()
            ->paginate($perPage);

        // TODO: response structure can be uniform?
        return JobListingResource::collection($jobListings);
    }

    public function show(JobListing $jobListing)
    {
        if ($jobListing->status !== JobListingStatus::PUBLISHED) {
            return response()->json([
                'message' => 'Job not found'
            ], 404);
        }

        $jobListing->load('user');

        return new JobListingResource($jobListing);
    }
}
