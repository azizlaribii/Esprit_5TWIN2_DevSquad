<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeDonation;
use App\Models\Donation;
use App\Models\DonationMatch;
use App\Services\Geocoder;
use App\Support\Textile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function index(Request $request): View
    {
        $donations = $request->user()->donations()
            ->with('photos')
            ->latest()
            ->paginate(10);

        return view('don.donations.index', compact('donations'));
    }

    public function create(): View
    {
        return view('don.donations.create');
    }

    public function store(Request $request, Geocoder $geocoder): RedirectResponse
    {
        $user = $request->user();

        // Anti-abus : plafond quotidien de dons par particulier.
        $recent = $user->donations()->where('created_at', '>=', now()->subDay())->count();
        if ($recent >= (int) config('textilecycle.max_donations_per_day')) {
            throw ValidationException::withMessages([
                'title' => 'Vous avez atteint la limite de dons pour aujourd\'hui. Réessayez demain.',
            ]);
        }

        $data = $request->validate($this->attributeRules() + [
            'photos'   => ['required', 'array', 'min:1', 'max:' . config('textilecycle.max_photos')],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . config('textilecycle.max_photo_kb')],
        ]);

        $coords = $this->coordinates($data, $geocoder);

        $donation = DB::transaction(function () use ($user, $data, $coords, $request) {
            $donation = $user->donations()->create(array_merge(Arr::except($data, ['photos']), [
                'lat'    => $coords['lat'] ?? null,
                'lng'    => $coords['lng'] ?? null,
                'status' => Donation::PENDING_ANALYSIS,
            ]));

            foreach ($request->file('photos') as $file) {
                $donation->photos()->create([
                    'path' => $file->store("donations/{$donation->id}", 'public'),
                ]);
            }

            return $donation;
        });

        AnalyzeDonation::dispatch($donation);

        return redirect()->route('donations.show', $donation)
            ->with('status', 'Merci ! Nous cherchons les associations les plus adaptées à votre don.');
    }

    public function show(Donation $donation): View
    {
        Gate::authorize('view', $donation);

        $donation->load([
            'photos',
            'matches' => fn ($q) => $q->orderByDesc('score'),
            'matches.association',
            'matches.need',
        ]);

        return view('don.donations.show', compact('donation'));
    }

    public function edit(Donation $donation): View
    {
        Gate::authorize('update', $donation);

        return view('don.donations.edit', compact('donation'));
    }

    /** Le donateur corrige ou complète les caractéristiques, puis le matching est relancé. */
    public function update(Request $request, Donation $donation, Geocoder $geocoder): RedirectResponse
    {
        Gate::authorize('update', $donation);

        $data = $request->validate($this->attributeRules());

        $cityChanged = $data['city'] !== $donation->city;
        $coords      = $cityChanged || $donation->lat === null
            ? $this->coordinates($data, $geocoder)
            : ['lat' => $donation->lat, 'lng' => $donation->lng];

        $donation->update(array_merge($data, [
            'lat'    => $coords['lat'] ?? null,
            'lng'    => $coords['lng'] ?? null,
            'status' => Donation::PENDING_ANALYSIS,
        ]));

        AnalyzeDonation::dispatch($donation);

        return redirect()->route('donations.show', $donation)
            ->with('status', 'Don mis à jour, nous recalculons les suggestions.');
    }

    /** Relance le matching sans rien modifier (ex. après l'arrivée d'un nouveau besoin). */
    public function rematch(Donation $donation): RedirectResponse
    {
        Gate::authorize('update', $donation);

        $donation->update(['status' => Donation::PENDING_ANALYSIS]);
        AnalyzeDonation::dispatch($donation);

        return redirect()->route('donations.show', $donation)
            ->with('status', 'Recherche relancée.');
    }

    public function destroy(Donation $donation): RedirectResponse
    {
        Gate::authorize('cancel', $donation);

        DB::transaction(function () use ($donation) {
            $donation->matches()
                ->whereIn('status', [DonationMatch::SUGGESTED, DonationMatch::REQUESTED])
                ->update(['status' => DonationMatch::CANCELLED]);

            $donation->update(['status' => Donation::CANCELLED]);
        });

        return redirect()->route('donations.index')->with('status', 'Don annulé.');
    }

    /** @return array<string, array<int, mixed>> */
    private function attributeRules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:200'],
            'city'        => ['required', 'string', 'max:100'],
            'lat'         => ['nullable', 'numeric', 'between:-90,90'],
            'lng'         => ['nullable', 'numeric', 'between:-180,180'],
            'category'    => ['nullable', Rule::in(array_keys(Textile::CATEGORIES))],
            'age_group'   => ['nullable', Rule::in(array_keys(Textile::AGE_GROUPS))],
            'size'        => ['nullable', 'string', 'max:20'],
            'gender'      => ['nullable', Rule::in(array_keys(Textile::GENDERS))],
            'season'      => ['nullable', Rule::in(array_keys(Textile::SEASONS))],
            'condition'   => ['nullable', Rule::in(array_keys(Textile::CONDITIONS))],
        ];
    }

    /**
     * Position fournie par le navigateur si présente, sinon géocodage de la ville.
     *
     * @return array{lat?: float, lng?: float}
     */
    private function coordinates(array $data, Geocoder $geocoder): array
    {
        if (isset($data['lat'], $data['lng'])) {
            return ['lat' => (float) $data['lat'], 'lng' => (float) $data['lng']];
        }

        return $geocoder->lookup($data['city']) ?? [];
    }
}
