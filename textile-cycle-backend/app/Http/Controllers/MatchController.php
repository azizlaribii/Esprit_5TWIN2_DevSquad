<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\DonationMatch;
use App\Notifications\NewDonationMatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MatchController extends Controller
{
    /** Le donateur choisit une association parmi les suggestions. */
    public function choose(Donation $donation, DonationMatch $donationMatch): RedirectResponse
    {
        abort_unless($donationMatch->donation_id === $donation->id, 404);
        Gate::authorize('choose', $donationMatch);

        if ($donation->status !== Donation::MATCHED || $donationMatch->status !== DonationMatch::SUGGESTED) {
            return back()->withErrors(['match' => 'Cette suggestion n\'est plus disponible.']);
        }

        DB::transaction(function () use ($donation, $donationMatch) {
            $donationMatch->update(['status' => DonationMatch::REQUESTED]);
            $donation->update(['status' => Donation::REQUESTED]);
        });

        $donationMatch->association->user->notify(new NewDonationMatch($donationMatch));

        return redirect()->route('donations.show', $donation)
            ->with('status', 'Demande envoyée à ' . $donationMatch->association->name . '. Vous serez prévenu de sa réponse.');
    }
}
