<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Enums\JobListingStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\JobListingResource;
use App\Models\JobListing;
use Illuminate\Http\Request;

class JobListingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $query = JobListing::with('user')->withCount('applications')->published();

        // has to do optional chaining here, because not all visitors are users
        // this is for public api
        if ($request->user()?->role === UserRole::APPLICANT) {
            // compare with enum object since we have casted in model
            $query->with([
                'applications' => fn($q) =>
                $q->where('user_id', $request->user()->id)
            ]);
        }
        // TODO: double check with frontend, should we response has_applied equals false always for uniformity?
        $jobListings = $query->latest()->paginate($perPage);

        return JobListingResource::collection($jobListings);
    }

    public function show(JobListing $jobListing)
    {
        if ($jobListing->status !== JobListingStatus::PUBLISHED) {
            return response()->json([
                'message' => 'Job not found'
            ], 404);
        }

        $jobListing->load('user')->loadCount('applications');

        return new JobListingResource($jobListing);
    }
}
