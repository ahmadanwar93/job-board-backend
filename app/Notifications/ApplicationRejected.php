<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationRejected extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Application $application) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->application->jobListing->title;
        $companyName = $this->application->jobListing->user->name;

        return (new MailMessage)
            ->subject('Application Update - ' . $jobTitle)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Thank you for your interest in the **' . $jobTitle . '** position at **' . $companyName . '**.')
            ->line('After careful consideration, we have decided to move forward with other candidates.')
            ->line('We appreciate the time you took to apply and wish you the best in your job search.')
            ->salutation('');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_rejected',
            'message' => "Your application for {$this->application->jobListing->title} was not successful",
            'job_id' => $this->application->job_listing_id,
            'application_id' => $this->application->id,
        ];
    }
}
