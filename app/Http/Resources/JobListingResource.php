<?php

namespace App\Http\Resources;

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
                $request->user()?->role === 'applicant',
                fn() => $this->applications()->where('user_id', $request->user()->id)->exists()
            ),
            // TODO: can we eager loading count here?
            'applications_count' => $this->when(
                $request->user()?->id === $this->user_id,
                fn() => $this->applications_count ?? $this->applications()->count()
            ),
        ];
    }
}
