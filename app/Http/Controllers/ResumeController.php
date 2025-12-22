<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobListing;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResumeController extends Controller
{
    use AuthorizesRequests;
    public function upload(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->can_upload) {
            return response()->json(['message' => 'You do not have permission to upload resumes'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240'
        ]);


        if ($user->resume_path) {
            Storage::disk('r2')->delete($user->resume_path);
        }

        $path = $request->file('file')->store("{$user->id}", 'r2');

        $user->update(['resume_path' => $path]);

        return response()->json(['message' => 'Resume uploaded successfully'], 201);
    }

    public function download(): StreamedResponse|JsonResponse
    {
        $user = request()->user();

        if (!$user->can_upload) {
            return response()->json(['message' => 'You do not have permission to access resumes'], 403);
        }
        if (!$user->resume_path || !Storage::disk('r2')->exists($user->resume_path)) {
            abort(404, 'No resume found');
        }

        return Storage::disk('r2')->download($user->resume_path, basename($user->resume_path));
    }

    public function downloadApplicantResume(JobListing $jobListing, Application $application)
    {
        $this->authorize('downloadResume', $application);

        if ($application->job_listing_id !== $jobListing->id) {
            return response()->json(['message' => 'Application does not belong to this job'], 404);
        }

        $applicant = $application->user;

        if (!$applicant->resume_path || !Storage::disk('r2')->exists($applicant->resume_path)) {
            return response()->json(['message' => 'Applicant has not uploaded a resume'], 404);
        }

        $filename = "{$applicant->name}_resume." . pathinfo($applicant->resume_path, PATHINFO_EXTENSION);

        return Storage::disk('r2')->download($applicant->resume_path, $filename);
    }
}
