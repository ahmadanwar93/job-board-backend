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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Application Received')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a new application for your job posting.')
            ->line('**Job:** ' . $this->application->jobListing->title)
            ->line('**Applicant:** ' . $this->application->user->name)
            ->line('**Applied on:** ' . $this->application->created_at->format('F j, Y'))
            ->action('View Application', url('/employer/jobs/' . $this->application->jobListing->id . '/applications'))
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
            //
        ];
    }
}
