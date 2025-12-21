<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationReceived extends Notification implements ShouldQueue
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
        $frontendUrl = config('app.frontend_url') . '/dashboard';
        return (new MailMessage)
            ->subject('New Application Received')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a new application for your job posting.')
            ->line('**Job:** ' . $this->application->jobListing->title)
            ->line('**Applicant:** ' . $this->application->user->name)
            ->line('**Applied on:** ' . $this->application->created_at->format('F j, Y'))
            ->action('View Application', $frontendUrl)
            ->line('Thank you for using our job board!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_received',
            'message' => "{$this->application->user->name} applied for {$this->application->jobListing->title}",
            'job_id' => $this->application->job_listing_id,
            'application_id' => $this->application->id,
        ];
    }
}
