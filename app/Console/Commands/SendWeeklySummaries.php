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

        $applicants = User::applicants()->get();

        // TODO: make it eager loading
        foreach ($applicants as $applicant) {
            $applications = Application::where('user_id', $applicant->id)
                ->where('created_at', '>=', $weekAgo)
                ->get();

            $applicationsCount = $applications->count();
            $viewedCount = $applications->whereNotNull('viewed_at')->count();
            $shortlistedCount = $applications->where('status', ApplicationStatus::SHORTLISTED)->count();
            $rejectedCount = $applications->where('status', ApplicationStatus::REJECTED)->count();

            if ($applicationsCount > 0) {
                $applicant->notify(new WeeklyApplicantSummary(
                    $applicationsCount,
                    $viewedCount,
                    $shortlistedCount,
                    $rejectedCount
                ));
            }
        }

        $this->info('Sent summaries to ' . $applicants->count() . ' applicants');

        // Send to employers
        $employers = User::employers()->get();

        foreach ($employers as $employer) {
            $jobsPosted = JobListing::where('user_id', $employer->id)
                ->where('created_at', '>=', $weekAgo)
                ->count();

            $jobIds = JobListing::where('user_id', $employer->id)->pluck('id');

            $applications = Application::whereIn('job_listing_id', $jobIds)
                ->where('created_at', '>=', $weekAgo)
                ->get();

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
