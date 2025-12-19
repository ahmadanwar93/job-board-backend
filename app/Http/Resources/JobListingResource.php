<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobListingResource extends JsonResource
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
            'description' => $this->description,
            'location' => $this->location,
            'salary_range' => $this->salary_range,
            'is_remote' => $this->is_remote,
            'status' => $this->status,
            'employer' => [
                'id' => $this->user_id,
                'name' => $this->user->name,
            ],
            'created_at' => $this->created_at->toISOString(),
            'has_applied' => $this->when(
                $request->user()?->role === UserRole::APPLICANT,
                fn() => $this->applications->isNotEmpty()
            ),
            'my_application' => $this->when(
                $request->user()?->role === UserRole::APPLICANT && $this->applications->isNotEmpty(),
                fn() => [
                    'status' => $this->applications->first()->status->value,
                    'message' => $this->applications->first()->message,
                    'viewed_at' => $this->applications->first()->viewed_at?->toISOString(),
                ]
            ),
            'applications_count' => $this->applications_count
        ];
    }
}
