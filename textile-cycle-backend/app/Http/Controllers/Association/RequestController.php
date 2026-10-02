<?php

namespace App\Http\Controllers\Association;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzeDonation;
use App\Models\Donation;
use App\Models\DonationMatch;
use App\Notifications\MatchAccepted;
use App\Notifications\MatchRejected;
use App\Support\Textile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Traitement d'une demande de don par l'association. */
class RequestController extends Controller
{
    public function show(DonationMatch $donationMatch): View
    {
        Gate::authorize('respond', $donationMatch);

        $donationMatch->load(['donation.photos', 'donation.user', 'need', 'association']);

        return view('don.association.request', ['match' => $donationMatch]);
    }

    public function accept(Request $request, DonationMatch $donationMatch): RedirectResponse
    {
        Gate::authorize('respond', $donationMatch);

        if (! $this->isPending($donationMatch)) {
            return back()->withErrors(['match' => 'Cette demande n\'est plus en attente.']);
        }

        $data = $request->validate([
            'meeting_type' => ['required', Rule::in(array_keys(Textile::MEETING_TYPES))],
            'meeting_at'   => ['required', 'date', 'after:now'],
            'meeting_note' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($donationMatch, $data) {
            $donationMatch->update($data + [
                'status'       => DonationMatch::ACCEPTED,
                'responded_at' => now(),
            ]);
            $donationMatch->donation->update(['status' => Donation::ACCEPTED]);
        });

        $donationMatch->donation->user->notify(new MatchAccepted($donationMatch->fresh(['donation', 'association'])));

        return redirect()->route('association.dashboard')
            ->with('status', 'Don accepté. Les coordonnées du donateur sont maintenant visibles.');
    }

    public function reject(DonationMatch $donationMatch): RedirectResponse
    {
        Gate::authorize('respond', $donationMatch);

        if (! $this->isPending($donationMatch)) {
            return back()->withErrors(['match' => 'Cette demande n\'est plus en attente.']);
        }

        $donation = $donationMatch->donation;

        DB::transaction(function () use ($donationMatch, $donation) {
            $donationMatch->update(['status' => DonationMatch::REJECTED, 'responded_at' => now()]);
            // Repasse en « analyse » : le job recalcule des suggestions en excluant cette association.
            $donation->update(['status' => Donation::PENDING_ANALYSIS]);
        });

        AnalyzeDonation::dispatch($donation);
        $donation->user->notify(new MatchRejected($donationMatch->fresh(['donation', 'association'])));

        return redirect()->route('association.dashboard')->with('status', 'Demande déclinée.');
    }

    /** L'association confirme avoir reçu les vêtements. */
    public function complete(DonationMatch $donationMatch): RedirectResponse
    {
        Gate::authorize('respond', $donationMatch);

        if ($donationMatch->status !== DonationMatch::ACCEPTED) {
            return back()->withErrors(['match' => 'Seul un don accepté peut être marqué comme remis.']);
        }

        DB::transaction(function () use ($donationMatch) {
            $donationMatch->update(['status' => DonationMatch::COMPLETED]);
            $donationMatch->donation->update(['status' => Donation::COMPLETED]);

            // Le besoin correspondant diminue de la quantité reçue.
            if ($need = $donationMatch->need) {
                $need->update(['quantity_needed' => max(0, $need->quantity_needed - $donationMatch->quantity)]);
            }
        });

        return redirect()->route('association.dashboard')->with('status', 'Don marqué comme remis. Merci !');
    }

    private function isPending(DonationMatch $match): bool
    {
        return $match->status === DonationMatch::REQUESTED
            && $match->donation->status === Donation::REQUESTED;
    }
}
