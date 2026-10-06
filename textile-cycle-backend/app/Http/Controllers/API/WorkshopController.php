<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RepairRequest;
use App\Models\Workshop;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    public function index(Request $request)
    {
        $query = Workshop::query();

        if ($request->filled('lat') && $request->filled('lng')) {
            $query->nearestFirst((float) $request->lat, (float) $request->lng);
        } else {
            $query->orderByDesc('rating');
        }

        $workshops = $query->limit(100)->get();
        return response()->json(['data' => $workshops]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:50'],
        ]);

        $workshop = Workshop::create([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'city' => $validated['city'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'specialties' => $validated['specialties'] ?? ['Général'],
            'rating' => 5.0,
        ]);

        return response()->json(['data' => $workshop], 201);
    }

    public function show(Workshop $workshop)
    {
        return response()->json(['data' => $workshop]);
    }

    public function update(Request $request, Workshop $workshop)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:50'],
        ]);

        $workshop->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'city' => $validated['city'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'specialties' => $validated['specialties'] ?? $workshop->specialties,
        ]);

        return response()->json(['data' => $workshop]);
    }

    public function destroy(Workshop $workshop)
    {
        $workshop->delete();
        return response()->json(['message' => 'Atelier supprimé avec succès']);
    }

    /**
     * GET /api/reparations/{repairRequest}/ateliers?lat=..&lng=..
     */
    public function forRepair(Request $request, RepairRequest $repairRequest)
    {
        $this->authorizeOwner($request, $repairRequest);

        $query = Workshop::query();

        // Compatibilité unicode escapé en base (JSON stocké avec \uXXXX)
        // On utilise LIKE comme fallback robuste car JSON_CONTAINS échoue sur les accents escapés
        $query->when($repairRequest->defect_type, function ($q) use ($repairRequest) {
            $defect  = '%' . $repairRequest->defect_type . '%';
            $general = '%Général%';
            $q->where(function ($sub) use ($defect, $general) {
                $sub->where('specialties', 'like', $defect)
                    ->orWhere('specialties', 'like', $general);
            });
        });

        if ($request->filled('lat') && $request->filled('lng')) {
            $query->nearestFirst((float) $request->lat, (float) $request->lng);
        } else {
            $query->orderByDesc('rating');
        }

        $workshops = $query->limit(50)->get();

        return response()->json(['data' => $workshops]);
    }

    /**
     * POST /api/reparations/{repairRequest}/ateliers/choisir  { workshop_id }
     */
    public function choose(Request $request, RepairRequest $repairRequest)
    {
        $this->authorizeOwner($request, $repairRequest);

        $validated = $request->validate([
            'workshop_id' => ['required', 'exists:workshops,id'],
        ]);

        $repairRequest->update([
            'workshop_id' => $validated['workshop_id'],
            'status' => 'atelier_choisi',
        ]);

        return response()->json(['data' => $repairRequest->load('workshop')]);
    }

    private function authorizeOwner(Request $request, RepairRequest $repairRequest): void
    {
        if (is_null($repairRequest->user_id)) {
            return;
        }

        $userId = $request->user()?->id ?? auth()->id();
        if ($userId) {
            abort_unless((int) $repairRequest->user_id === (int) $userId, 403, 'Action non autorisée');
        }
    }
}