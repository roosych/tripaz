<?php

namespace App\Notifications;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Listing $listing,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->listing->translation()?->title ?? $this->listing->slug;

        return (new MailMessage)
            ->subject('Your payment has been approved — ' . $title)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . '!')
            ->line('Great news! Your payment for the listing "' . $title . '" has been approved.')
            ->line('Your listing is now published and visible to visitors.')
            ->action('View Listing', url('/listings/' . $this->listing->slug))
            ->line('Thank you for using our platform.');
    }
}
