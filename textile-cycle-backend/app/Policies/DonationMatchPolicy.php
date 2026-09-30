<?php

namespace App\Policies;

use App\Models\DonationMatch;
use App\Models\User;

class DonationMatchPolicy
{
    /** Le donateur choisit cette suggestion. */
    public function choose(User $user, DonationMatch $match): bool
    {
        return $match->donation->user_id === $user->id;
    }

    /** L'association concernée répond à la demande (accepter, refuser, marquer remis). */
    public function respond(User $user, DonationMatch $match): bool
    {
        return $user->isAssociation() && $match->association->user_id === $user->id;
    }
}
