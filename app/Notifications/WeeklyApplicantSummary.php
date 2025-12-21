<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyApplicantSummary extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public int $applicationsCount,
        public int $viewedCount,
        public int $shortlistedCount,
        public int $rejectedCount
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
            ->subject('Your Weekly Job Application Summary')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Here is your weekly job application summary:')
            ->line('**Applications submitted:** ' . $this->applicationsCount)
            ->line('**Applications viewed by employers:** ' . $this->viewedCount)
            ->line('**Applications shortlisted:** ' . $this->shortlistedCount)
            ->line('**Applications rejected:** ' . $this->rejectedCount)
            ->action('View all applications', $frontendUrl)
            ->line('Keep up the good work!');
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
