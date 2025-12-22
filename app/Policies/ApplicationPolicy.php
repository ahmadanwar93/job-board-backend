<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Application;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::APPLICANT;
    }

    public function view(User $user, Application $application): bool
    {
        return $user->id === $application->user_id
            || $user->id === $application->jobListing->user_id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::APPLICANT;
    }

    public function markAsViewed(User $user, Application $application): bool
    {
        return $user->role === UserRole::EMPLOYER
            && $user->id === $application->jobListing->user_id;
    }

    public function updateStatus(User $user, Application $application): bool
    {
        return $user->role === UserRole::EMPLOYER
            && $user->id === $application->jobListing->user_id;
    }

    public function downloadResume(User $user, Application $application): bool
    {
        return $user->role === UserRole::EMPLOYER
            && $user->id === $application->jobListing->user_id;
    }
}
