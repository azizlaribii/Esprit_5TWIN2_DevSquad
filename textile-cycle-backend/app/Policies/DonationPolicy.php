<?php

namespace App\Policies;

use App\Models\Donation;
use App\Models\User;

class DonationPolicy
{
    public function view(User $user, Donation $donation): bool
    {
        return $user->id === $donation->user_id || $user->isAdmin();
    }

    /** Modifiable tant qu'aucune association n'a répondu. */
    public function update(User $user, Donation $donation): bool
    {
        return $user->id === $donation->user_id && $donation->isEditable();
    }

    /** Annulable avant l'acceptation par une association. */
    public function cancel(User $user, Donation $donation): bool
    {
        return $user->id === $donation->user_id
            && ($donation->isEditable() || $donation->status === Donation::REQUESTED);
    }

    /** Suppression définitive : tant que le don n'est ni accepté ni remis. */
    public function delete(User $user, Donation $donation): bool
    {
        return $user->id === $donation->user_id
            && ! in_array($donation->status, [Donation::ACCEPTED, Donation::COMPLETED], true);
    }

    /** Le donateur choisit une association parmi les suggestions. */
    public function choose(User $user, Donation $donation): bool
    {
        return $user->id === $donation->user_id;
    }
}
