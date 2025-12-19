<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class JobListingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::EMPLOYER;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JobListing $jobListing): bool
    {
        return $user->id === $jobListing->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::EMPLOYER;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JobListing $jobListing): bool
    {
        return $user->role === UserRole::EMPLOYER
            && $user->id === $jobListing->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JobListing $jobListing): bool
    {
        return $user->role === UserRole::EMPLOYER
            && $user->id === $jobListing->user_id;
    }

    public function viewApplications(User $user, JobListing $jobListing): bool
    {
        // only employer can see all the applicants
        return $user->id === $jobListing->user_id;
    }
}
