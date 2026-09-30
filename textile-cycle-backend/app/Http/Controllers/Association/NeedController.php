<?php

namespace App\Http\Controllers\Association;

use App\Http\Controllers\Controller;
use App\Models\AssociationNeed;
use App\Support\Textile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NeedController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $association = $request->user()->association;

        if (! $association) {
            return redirect()->route('association.profile.edit')
                ->with('status', 'Renseignez d\'abord le profil de votre association.');
        }

        return view('don.association.needs', [
            'association' => $association,
            'needs'       => $association->needs()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $association = $request->user()->association;
        abort_unless($association, 403);

        $data = $request->validate([
            'category'        => ['required', Rule::in(array_keys(Textile::CATEGORIES))],
            'age_group'       => ['nullable', Rule::in(array_keys(Textile::AGE_GROUPS))],
            'size'            => ['nullable', 'string', 'max:20'],
            'gender'          => ['nullable', Rule::in(array_keys(Textile::GENDERS))],
            'season'          => ['nullable', Rule::in(array_keys(Textile::SEASONS))],
            'quantity_needed' => ['required', 'integer', 'min:1', 'max:100000'],
            'urgency'         => ['required', 'integer', 'between:1,5'],
            'expires_at'      => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $association->needs()->create($data);

        return back()->with('status', 'Besoin ajouté.');
    }

    public function destroy(Request $request, AssociationNeed $need): RedirectResponse
    {
        abort_unless($request->user()->association?->id === $need->association_id, 403);

        $need->delete();

        return back()->with('status', 'Besoin supprimé.');
    }
}
