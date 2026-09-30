<?php

namespace App\Notifications;

use App\Models\DonationMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Envoyée au donateur quand l'association décline : de nouvelles suggestions sont calculées. */
class MatchRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DonationMatch $match)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre don n\'a pas pu être accepté')
            ->line(sprintf(
                '%s ne peut pas accepter votre don « %s » pour le moment.',
                $this->match->association->name,
                $this->match->donation->title
            ))
            ->line('Nous recalculons de nouvelles suggestions pour vous.')
            ->action('Voir les nouvelles suggestions', route('donations.show', $this->match->donation_id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'match_id' => $this->match->id,
            'message'  => $this->match->association->name . ' ne peut pas accepter « ' . $this->match->donation->title . ' ».',
            'url'      => route('donations.show', $this->match->donation_id),
        ];
    }
}
