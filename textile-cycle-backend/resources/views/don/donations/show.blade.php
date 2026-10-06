@extends('layouts.don')

@section('title', $donation->title)

@section('head')
    {{-- Tant que l'analyse tourne en file d'attente, on rafraîchit la page toutes les 5 s. --}}
    @if ($donation->status === \App\Models\Donation::PENDING_ANALYSIS)
        <meta http-equiv="refresh" content="5">
    @endif
@endsection

@section('content')
@php
    use App\Models\Donation;
    use App\Models\DonationMatch;
    use App\Support\Textile;

    $suggested = $donation->matches->where('status', DonationMatch::SUGGESTED);
    $chosen    = $donation->matches->first(fn ($m) => in_array($m->status, [
        DonationMatch::REQUESTED, DonationMatch::ACCEPTED, DonationMatch::COMPLETED,
    ], true));
    $rejected  = $donation->matches->where('status', DonationMatch::REJECTED);
    $aiFilled  = $donation->ai_metadata['filled'] ?? [];
    $aiNotes   = $donation->ai_metadata['result']['notes'] ?? null;
    $aiConf    = $donation->ai_metadata['confidence'] ?? null;
@endphp

<a href="{{ route('donations.index') }}" class="text-sm text-stone-500 hover:underline">← Mes dons</a>

<div class="mt-2 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold">{{ $donation->title }}</h1>
        <p class="mt-1 text-sm text-stone-500">{{ $donation->quantity }} pièce(s) · {{ $donation->city }} · déposé le {{ $donation->created_at->format('d/m/Y') }}</p>
    </div>
    @include('don.partials.status-badge', ['status' => $donation->status, 'label' => $donation->statusLabel()])
</div>

{{-- Photos + caractéristiques --}}
<section class="mt-6 grid gap-6 rounded-lg border border-stone-200 bg-white p-5 md:grid-cols-2">
    <div class="grid grid-cols-3 gap-2">
        @foreach ($donation->photos as $photo)
            <img src="{{ $photo->url() }}" alt="Photo du don" class="aspect-square w-full rounded-md object-cover">
        @endforeach
    </div>

    <div>
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
            <dt class="text-stone-500">Catégorie</dt><dd>{{ Textile::label('categories', $donation->category) }}</dd>
            <dt class="text-stone-500">État</dt><dd>{{ Textile::label('conditions', $donation->condition) }}</dd>
            <dt class="text-stone-500">Public</dt><dd>{{ Textile::label('age_groups', $donation->age_group) }}</dd>
            <dt class="text-stone-500">Taille</dt><dd>{{ $donation->size ?: '—' }}</dd>
            <dt class="text-stone-500">Genre</dt><dd>{{ Textile::label('genders', $donation->gender) }}</dd>
            <dt class="text-stone-500">Saison</dt><dd>{{ Textile::label('seasons', $donation->season) }}</dd>
        </dl>

        @if ($donation->description)
            <p class="mt-4 text-sm text-stone-600">{!! nl2br(e($donation->description)) !!}</p>
        @endif

        @if (count($aiFilled))
            <p class="mt-4 rounded-md bg-stone-50 p-3 text-xs text-stone-500">
                ✨ L'IA a renseigné : {{ collect($aiFilled)->map(fn ($f) => ['category' => 'la catégorie', 'age_group' => 'le public', 'size' => 'la taille', 'gender' => 'le genre', 'season' => 'la saison', 'condition' => 'l\'état'][$f] ?? $f)->join(', ', ' et ') }}
                @if ($aiConf !== null) (confiance {{ round($aiConf * 100) }} %) @endif.
                @if ($aiNotes) « {{ $aiNotes }} » @endif
                @can('update', $donation)
                    <a href="{{ route('donations.edit', $donation) }}" class="text-emerald-700 hover:underline">Corriger</a>
                @endcan
            </p>
        @endif

        <div class="mt-4 flex gap-4 text-sm">
            @can('update', $donation)
                <a href="{{ route('donations.edit', $donation) }}" class="text-emerald-700 hover:underline">Modifier</a>
            @endcan
            @can('delete', $donation)
                <form method="POST" action="{{ route('donations.destroy', $donation) }}"
                      onsubmit="return confirm('Supprimer définitivement ce don ?')">
                    @csrf @method('DELETE')
                    <button class="text-red-600 hover:underline">Supprimer le don</button>
                </form>
            @endcan
        </div>
    </div>
</section>

{{-- Bloc dépendant du statut --}}
@switch($donation->status)

    @case(Donation::PENDING_ANALYSIS)
        <section class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
            <p class="font-medium">Analyse en cours…</p>
            <p class="mt-1">Nous examinons vos photos et cherchons les associations les plus adaptées. Cette page se met à jour toute seule.</p>
        </section>
        @break

    @case(Donation::NEEDS_REVIEW)
        <section class="mt-6 rounded-lg border border-orange-200 bg-orange-50 p-5 text-sm text-orange-900">
            <p class="font-medium">Il manque quelques informations</p>
            <p class="mt-1">Indiquez la catégorie et l'état pour que nous puissions vous proposer des associations.</p>
            <a href="{{ route('donations.edit', $donation) }}" class="mt-3 inline-block rounded-md bg-orange-600 px-4 py-2 font-medium text-white hover:bg-orange-700">Compléter le don</a>
        </section>
        @break

    @case(Donation::NO_MATCH)
        <section class="mt-6 rounded-lg border border-stone-200 bg-white p-5 text-sm">
            <p class="font-medium">Aucune association compatible pour le moment</p>
            <p class="mt-1 text-stone-600">Aucune association vérifiée proche de vous n'accepte ce type de don actuellement. De nouveaux besoins sont publiés régulièrement.</p>
            <form method="POST" action="{{ route('donations.rematch', $donation) }}" class="mt-3">
                @csrf
                <button class="rounded-md bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">Relancer la recherche</button>
            </form>
        </section>
        @break

    @case(Donation::MATCHED)
        <section class="mt-8">
            <h2 class="text-lg font-semibold">Associations recommandées</h2>
            <p class="text-sm text-stone-500">Classées par pertinence. Choisissez celle à qui vous souhaitez proposer votre don.</p>

            <div class="mt-4 space-y-4">
                @foreach ($suggested as $match)
                    <article class="rounded-lg border border-stone-200 bg-white p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold">{{ $match->association->name }}</h3>
                                <p class="text-sm text-stone-500">{{ $match->association->city }}</p>
                            </div>
                            <div class="w-40">
                                <p class="text-right text-sm font-medium text-emerald-700">{{ round($match->score) }}/100</p>
                                <div class="mt-1 h-2 rounded-full bg-stone-100">
                                    <div class="h-2 rounded-full bg-emerald-500" style="width: {{ min(100, max(0, $match->score)) }}%"></div>
                                </div>
                            </div>
                        </div>

                        <p class="mt-3 text-sm text-stone-700">{{ $match->displayExplanation() }}</p>

                        @if ($match->explanation && $match->reasons)
                            <details class="mt-2 text-xs text-stone-500">
                                <summary class="cursor-pointer">Détail du calcul</summary>
                                <ul class="mt-1 list-inside list-disc">
                                    @foreach ($match->reasons as $reason)<li>{{ $reason }}</li>@endforeach
                                </ul>
                            </details>
                        @endif

                        <form method="POST" action="{{ route('matches.choose', [$donation, $match]) }}" class="mt-4">
                            @csrf
                            <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                                Proposer mon don à cette association
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>
        </section>
        @break

    @case(Donation::REQUESTED)
        <section class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-5 text-sm text-blue-900">
            <p class="font-medium">Demande envoyée à {{ $chosen?->association->name }}</p>
            <p class="mt-1">Vous serez prévenu par e-mail dès qu'elle aura répondu. Si elle décline, nous recalculons automatiquement de nouvelles suggestions.</p>
        </section>
        @break

    @case(Donation::ACCEPTED)
    @case(Donation::COMPLETED)
        @if ($chosen)
            <section class="mt-6 rounded-lg border border-green-200 bg-green-50 p-5 text-sm text-green-900">
                @if ($donation->status === Donation::COMPLETED)
                    <p class="font-medium">Don remis à {{ $chosen->association->name }}. Merci pour votre geste !</p>
                @else
                    <p class="font-medium">{{ $chosen->association->name }} a accepté votre don</p>
                @endif

                <dl class="mt-3 grid grid-cols-[8rem_1fr] gap-y-1">
                    <dt class="text-green-700">Modalité</dt><dd>{{ Textile::MEETING_TYPES[$chosen->meeting_type] ?? '—' }}</dd>
                    <dt class="text-green-700">Date</dt><dd>{{ $chosen->meeting_at?->format('d/m/Y à H:i') ?? '—' }}</dd>
                    @if ($chosen->meeting_note)
                        <dt class="text-green-700">Précisions</dt><dd>{{ $chosen->meeting_note }}</dd>
                    @endif
                    <dt class="text-green-700">Ville</dt><dd>{{ $chosen->association->city }}</dd>
                    @if ($chosen->association->phone)
                        <dt class="text-green-700">Téléphone</dt><dd>{{ $chosen->association->phone }}</dd>
                    @endif
                    @if ($chosen->association->opening_hours)
                        <dt class="text-green-700">Horaires</dt><dd>{{ $chosen->association->opening_hours }}</dd>
                    @endif
                </dl>
            </section>
        @endif
        @break

@endswitch

@if ($rejected->isNotEmpty())
    <section class="mt-6 text-sm text-stone-500">
        <p class="font-medium text-stone-600">Associations ayant décliné</p>
        <ul class="mt-1 list-inside list-disc">
            @foreach ($rejected as $match)<li>{{ $match->association->name }}</li>@endforeach
        </ul>
    </section>
@endif
@endsection
