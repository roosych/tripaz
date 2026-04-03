<?php

namespace App\Notifications;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Listing $listing,
        public readonly string $reason = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->listing->translation()?->title ?? $this->listing->slug;

        $mail = (new MailMessage)
            ->subject('Payment rejected — action required for ' . $title)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . '!')
            ->line('Unfortunately, your payment proof for the listing "' . $title . '" was rejected.');

        if ($this->reason !== '') {
            $mail->line('Reason: ' . $this->reason);
        }

        return $mail
            ->line('You can submit a new payment proof from your dashboard.')
            ->action('Submit Payment', url('/owner/listings/' . $this->listing->id . '/payment'))
            ->line('Please contact support if you believe this is a mistake.');
    }
}
