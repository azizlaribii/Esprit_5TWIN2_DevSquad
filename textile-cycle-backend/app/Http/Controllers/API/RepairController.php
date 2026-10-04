<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RepairRequest;
use App\Services\DefectDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RepairController extends Controller
{
    // Utilisateur par défaut (temporaire, en attendant l'auth du projet principal)
    private const DEFAULT_USER_ID = 1;

    public function __construct(private DefectDetectionService $ai)
    {
    }

    /**
     * GET /api/reparations
     */
    public function index(Request $request)
    {
        $repairRequests = RepairRequest::with('workshop')
            ->latest()
            ->get();

        return response()->json(['data' => $repairRequests]);
    }

    /**
     * POST /api/reparations  (multipart/form-data, champ "photo")
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'photo.required' => 'Merci d\'ajouter une photo du vêtement.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'Formats acceptés : jpg, jpeg, png, webp.',
            'photo.max' => 'La photo ne doit pas dépasser 5 Mo.',
        ]);

        $path = $request->file('photo')->store('repairs', 'public');
        $analysis = $this->ai->analyze($request->file('photo'));

        $repairRequest = RepairRequest::create([
            'user_id' => $request->user()?->id ?? auth()->id() ?? \App\Models\User::first()?->id ?? null,
            'photo_path' => $path,
            'ai_verdict' => $analysis['verdict'],
            'defect_type' => $analysis['defect_type'],
            'location' => $analysis['location'],
            'severity' => $analysis['severity'],
            'suggested_repairs' => $analysis['suggested_repairs'],
            'bbox' => $analysis['bbox'] ?? null,
            'estimated_cost_min' => $analysis['estimated_cost_min'],
            'estimated_cost_max' => $analysis['estimated_cost_max'],
            'confidence' => $analysis['confidence'],
            'status' => $analysis['verdict'] === 'defaut' ? 'analysee' : 'terminee',
        ]);

        return response()->json(['data' => $repairRequest], 201);
    }

    /**
     * GET /api/reparations/{repairRequest}
     */
    public function show(Request $request, RepairRequest $repairRequest)
    {
        return response()->json(['data' => $repairRequest->load('workshop')]);
    }

    /**
     * DELETE /api/reparations/{repairRequest}
     */
    public function destroy(Request $request, RepairRequest $repairRequest)
    {
        Storage::disk('public')->delete($repairRequest->photo_path);
        $repairRequest->delete();

        return response()->json(null, 204);
    }
}