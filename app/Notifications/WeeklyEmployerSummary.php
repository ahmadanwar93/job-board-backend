<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyEmployerSummary extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public int $jobsPosted,
        public int $applicationsReceived,
        public int $applicationsViewed,
        public int $applicationsShortlisted,
        public int $applicationsRejected
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url') . '/dashboard';
        return (new MailMessage)
            ->subject('Your Weekly Job Posting Summary')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Here is your weekly job posting summary:')
            ->line('**Jobs posted:** ' . $this->jobsPosted)
            ->line('**Applications received:** ' . $this->applicationsReceived)
            ->line('**Applications viewed:** ' . $this->applicationsViewed)
            ->line('**Applications shortlisted:** ' . $this->applicationsShortlisted)
            ->line('**Applications rejected:** ' . $this->applicationsRejected)
            ->action('View all applications', $frontendUrl)
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
