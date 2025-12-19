<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
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
            'message' => $this->message,
            'created_at' => $this->created_at->toISOString(),
            'viewed_at' => $this->viewed_at?->toISOString(),
            'status' => $this->status,
            'job' => $this->when(
                $request->user()?->role === UserRole::APPLICANT,
                fn() => [
                    'id' => $this->jobListing->id,
                    'title' => $this->jobListing->title,
                    'company' => $this->jobListing->user->name,
                    'status' => $this->jobListing->status,
                ]
            ),
            'applicant' => $this->when(
                $request->user()?->role === UserRole::EMPLOYER,
                fn() => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ]
            ),
        ];
    }
}
