<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobDropdownResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'applications_count' => $this->applications_count,
            'applications' => $this->applications->map(function ($application) {
                return [
                    'id' => $application->id,
                    'applicant_name' => $application->user->name,
                    'applicant_email' => $application->user->email,
                    'message' => $application->message,
                    'status' => $application->status->value,
                    'created_at' => $application->created_at->diffForHumans(),
                    'viewed_at' => $application->viewed_at?->diffForHumans(),
                    'has_resume' => !is_null($application->user->resume_path),
                ];
            }),
        ];
    }
}
