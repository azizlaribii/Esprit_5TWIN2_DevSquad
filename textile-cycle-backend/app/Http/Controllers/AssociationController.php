<?php

namespace App\Http\Controllers;

use App\Models\Association;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssociationController extends Controller
{
    /**
     * Store a new association (accessible from /associations and quick-add in donation form).
     * The new association appears on /associations immediately.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'nom'                => ['required', 'string', 'max:255'],
            'adresse'            => ['nullable', 'string', 'max:255'],
            'city'               => ['nullable', 'string', 'max:100'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'capacity'           => ['nullable', 'integer', 'min:1', 'max:10000'],
            'beneficiaires_aides'=> ['nullable', 'integer', 'min:0'],
        ], [
            'nom.required' => 'Le nom de l\'association est obligatoire.',
        ]);

        $association = Association::create([
            'nom'                 => $data['nom'],
            'adresse'             => $data['adresse'] ?? null,
            'city'                => $data['city'] ?? null,
            'description'         => $data['description'] ?? null,
            'phone'               => $data['phone'] ?? null,
            'capacity'            => $data['capacity'] ?? 100,
            'beneficiaires_aides' => $data['beneficiaires_aides'] ?? 0,
            'verified_at'         => now(),
        ]);

        if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success'     => true,
                'message'     => 'Association "' . $association->nom . '" ajoutée avec succès !',
                'association' => [
                    'id'          => $association->id,
                    'nom'         => $association->nom,
                    'city'        => $association->city,
                    'adresse'     => $association->adresse,
                    'phone'       => $association->phone,
                    'description' => $association->description,
                ],
            ], 201);
        }

        return redirect()->route('associations.index')
            ->with('success', '✅ Association "' . $data['nom'] . '" ajoutée avec succès !');
    }
}
