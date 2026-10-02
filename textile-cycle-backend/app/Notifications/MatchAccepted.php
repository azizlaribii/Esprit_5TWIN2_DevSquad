<?php

namespace App\Notifications;

use App\Models\DonationMatch;
use App\Support\Textile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Envoyée au donateur quand l'association accepte son don. */
class MatchAccepted extends Notification implements ShouldQueue
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
        $m = $this->match;

        $mail = (new MailMessage)
            ->subject('Votre don a été accepté')
            ->greeting('Bonne nouvelle !')
            ->line(sprintf('%s a accepté votre don « %s ».', $m->association->name, $m->donation->title))
            ->line(sprintf(
                '%s le %s.',
                Textile::MEETING_TYPES[$m->meeting_type] ?? 'Rendez-vous',
                $m->meeting_at?->timezone(config('app.timezone'))->format('d/m/Y à H:i')
            ));

        if ($m->meeting_note) {
            $mail->line('Précisions : ' . $m->meeting_note);
        }

        return $mail->action('Voir les détails', route('donations.show', $m->donation));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'match_id' => $this->match->id,
            'message'  => $this->match->association->name . ' a accepté votre don « ' . $this->match->donation->title . ' ».',
            'url'      => route('donations.show', $this->match->donation_id),
        ];
    }
}
