<?php

namespace App\Http\Controllers\Association;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationMatch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $association = $request->user()->association;

        if (! $association) {
            return redirect()->route('association.profile.edit')
                ->with('status', 'Commencez par renseigner le profil de votre association.');
        }

        // Le donateur reste anonyme tant que la demande n'est pas acceptée.
        $requests = $association->matches()
            ->where('status', DonationMatch::REQUESTED)
            ->whereHas('donation', fn ($q) => $q->where('status', Donation::REQUESTED))
            ->with(['donation.photos', 'need'])
            ->latest()
            ->get();

        $accepted = $association->matches()
            ->where('status', DonationMatch::ACCEPTED)
            ->with('donation')
            ->orderBy('meeting_at')
            ->get();

        $completed = $association->matches()->where('status', DonationMatch::COMPLETED);

        return view('don.association.dashboard', [
            'association'    => $association,
            'requests'       => $requests,
            'accepted'       => $accepted,
            'completedCount' => (clone $completed)->count(),
            'piecesReceived' => (int) (clone $completed)->sum('quantity'),
            'needsCount'     => $association->activeNeeds()->count(),
        ]);
    }
}
