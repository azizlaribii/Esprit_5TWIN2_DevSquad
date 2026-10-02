<?php

namespace App\Notifications;

use App\Models\DonationMatch;
use App\Support\Textile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Envoyée à l'association quand un donateur la choisit. */
class NewDonationMatch extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DonationMatch $match)
    {
    }

    public function via(object $notifiable): array
    {
        // Ajouter 'database' après `php artisan make:notifications-table` pour afficher les alertes dans l'app.
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $donation = $this->match->donation;

        return (new MailMessage)
            ->subject('Nouvelle proposition de don')
            ->greeting('Bonjour,')
            ->line(sprintf(
                'Un donateur souhaite vous remettre %d pièce(s) : %s (%s), à %s.',
                $donation->quantity,
                Textile::label('categories', $donation->category),
                Textile::label('conditions', $donation->condition),
                $donation->city
            ))
            ->action('Répondre à la demande', route('association.requests.show', $this->match))
            ->line('Les coordonnées du donateur vous seront communiquées après votre acceptation.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'match_id' => $this->match->id,
            'message'  => 'Nouvelle proposition de don : ' . $this->match->donation->title,
            'url'      => route('association.requests.show', $this->match),
        ];
    }
}
