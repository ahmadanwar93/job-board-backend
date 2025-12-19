<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use App\Notifications\WeeklyApplicantSummary;
use App\Notifications\WeeklyEmployerSummary;
use Illuminate\Console\Command;

class SendWeeklySummaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-weekly-summaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly summaries to applicants and employers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $weekAgo = now()->subWeek();

        $applicants = User::applicants()
            ->whereHas('applications', function ($query) use ($weekAgo) {
                $query->where('created_at', '>=', $weekAgo);
            })
            ->with(['applications' => function ($query) use ($weekAgo) {
                $query->where('created_at', '>=', $weekAgo);
            }])
            ->get();

        foreach ($applicants as $applicant) {
            $applications = $applicant->applications;

            $applicationsCount = $applications->count();
            $viewedCount = $applications->whereNotNull('viewed_at')->count();
            $shortlistedCount = $applications->where('status', ApplicationStatus::SHORTLISTED)->count();
            $rejectedCount = $applications->where('status', ApplicationStatus::REJECTED)->count();

            $applicant->notify(new WeeklyApplicantSummary(
                $applicationsCount,
                $viewedCount,
                $shortlistedCount,
                $rejectedCount
            ));
        }

        $this->info("Sent summaries to {$applicants->count()} applicants");

        // Send to employers
        $employers = User::employers()
            ->where(function ($query) use ($weekAgo) {
                $query->whereHas('jobs', function ($q) use ($weekAgo) {
                    $q->where('created_at', '>=', $weekAgo);
                })->orWhereHas('jobs.applications', function ($q) use ($weekAgo) {
                    $q->where('created_at', '>=', $weekAgo);
                });
            })
            ->with([
                'jobs' => fn($q) => $q->where('created_at', '>=', $weekAgo),
                'jobs.applications' => fn($q) => $q->where('created_at', '>=', $weekAgo)
            ])
            ->get();

        foreach ($employers as $employer) {
            $jobsPosted = $employer->jobs->count();
            $applications = $employer->jobs->flatMap->applications;

            $applicationsReceived = $applications->count();
            $applicationsViewed = $applications->whereNotNull('viewed_at')->count();
            $applicationsShortlisted = $applications->where('status', ApplicationStatus::SHORTLISTED)->count();
            $applicationsRejected = $applications->where('status', ApplicationStatus::REJECTED)->count();

            if ($jobsPosted > 0 || $applicationsReceived > 0) {
                $employer->notify(new WeeklyEmployerSummary(
                    $jobsPosted,
                    $applicationsReceived,
                    $applicationsViewed,
                    $applicationsShortlisted,
                    $applicationsRejected
                ));
            }
        }

        $this->info('Sent summaries to ' . $employers->count() . ' employers');

        return 0;
    }
}
