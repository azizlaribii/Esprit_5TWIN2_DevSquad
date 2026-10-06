<?php

namespace App\Http\Controllers\Association;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Services\Geocoder;
use App\Support\Textile;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('don.association.profile', [
            'association' => $request->user()->association ?? new Association(),
        ]);
    }

    public function update(Request $request, Geocoder $geocoder): RedirectResponse
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:150'],
            'description'           => ['nullable', 'string', 'max:2000'],
            'city'                  => ['required', 'string', 'max:100'],
            'phone'                 => ['nullable', 'string', 'max:30'],
            'opening_hours'         => ['nullable', 'string', 'max:500'],
            'capacity'              => ['required', 'integer', 'min:1', 'max:100000'],
            'accepted_conditions'   => ['nullable', 'array'],
            'accepted_conditions.*' => [Rule::in(array_keys(Textile::CONDITIONS))],
            'accepted_categories'   => ['nullable', 'array'],
            'accepted_categories.*' => [Rule::in(array_keys(Textile::CATEGORIES))],
        ], ValidationMessages::messages(), ValidationMessages::attributes());

        $association = $request->user()->association;

        // Aucune case cochée = « tout accepter » (voir MatchingService::isEligible).
        $data['accepted_conditions'] = array_values($data['accepted_conditions'] ?? []);
        $data['accepted_categories'] = array_values($data['accepted_categories'] ?? []);

        if (! $association || $association->city !== $data['city'] || $association->lat === null) {
            $coords      = $geocoder->lookup($data['city']);
            $data['lat'] = $coords['lat'] ?? null;
            $data['lng'] = $coords['lng'] ?? null;
        }

        // verified_at n'est jamais modifiable ici : seul un admin vérifie une association.
        $request->user()->association()->updateOrCreate([], $data);

        return redirect()->route('association.dashboard')->with(
            'status',
            $association?->isVerified()
                ? 'Profil mis à jour.'
                : 'Profil enregistré. Votre association sera proposée aux donateurs après vérification par notre équipe.'
        );
    }
}
