@extends('layouts.don')

@section('title', 'Tableau de bord')

@section('content')
@php
    use App\Support\Textile;
@endphp

<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold">{{ $association->name }}</h1>
        <p class="text-sm text-stone-500">{{ $association->city }}</p>
    </div>
    @if ($association->isVerified())
        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800">Association vérifiée</span>
    @endif
</div>

@unless ($association->isVerified())
    <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Votre association est en cours de vérification par notre équipe. Tant qu'elle n'est pas vérifiée, elle n'est pas proposée aux donateurs.
    </div>
@endunless

<div class="mt-6 grid gap-4 sm:grid-cols-4">
    @foreach ([
        ['Demandes à traiter', $requests->count()],
        ['Dons à recevoir', $accepted->count()],
        ['Pièces reçues', $piecesReceived],
        ['Besoins ouverts', $needsCount],
    ] as [$text, $value])
        <div class="rounded-lg border border-stone-200 bg-white p-4">
            <p class="text-2xl font-semibold text-emerald-700">{{ $value }}</p>
            <p class="text-sm text-stone-500">{{ $text }}</p>
        </div>
    @endforeach
</div>

@if ($needsCount === 0)
    <p class="mt-4 text-sm text-stone-600">
        Vous n'avez aucun besoin ouvert : les donateurs ne verront votre association que pour les catégories que vous acceptez, avec un score plus bas.
        <a href="{{ route('association.needs.index') }}" class="text-emerald-700 hover:underline">Déclarer un besoin</a>
    </p>
@endif

<section class="mt-8">
    <h2 class="text-lg font-semibold">Demandes à traiter</h2>

    @forelse ($requests as $match)
        @php $donation = $match->donation; @endphp
        <a href="{{ route('association.requests.show', $match) }}"
           class="mt-3 flex items-center gap-4 rounded-lg border border-stone-200 bg-white p-4 hover:border-emerald-300">
            @if ($donation->photos->first())
                <img src="{{ $donation->photos->first()->url() }}" alt="" class="h-16 w-16 rounded-md object-cover">
            @endif
            <div class="min-w-0 flex-1">
                <p class="font-medium">{{ $donation->quantity }} × {{ Textile::label('categories', $donation->category) }}</p>
                <p class="text-sm text-stone-500">
                    {{ Textile::label('conditions', $donation->condition) }} · {{ $donation->city }} · reçue le {{ $match->updated_at->format('d/m/Y') }}
                </p>
            </div>
            <span class="text-sm font-medium text-emerald-700">Répondre →</span>
        </a>
    @empty
        <p class="mt-2 text-sm text-stone-500">Aucune demande en attente.</p>
    @endforelse
</section>

<section class="mt-8">
    <h2 class="text-lg font-semibold">Dons acceptés, en attente de remise</h2>

    @forelse ($accepted as $match)
        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-stone-200 bg-white p-4">
            <div>
                <p class="font-medium">{{ $match->quantity }} × {{ Textile::label('categories', $match->donation->category) }}</p>
                <p class="text-sm text-stone-500">
                    {{ Textile::MEETING_TYPES[$match->meeting_type] ?? '' }} · {{ $match->meeting_at?->format('d/m/Y à H:i') }}
                </p>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('association.requests.show', $match) }}" class="text-emerald-700 hover:underline">Détails et contact</a>
                <form method="POST" action="{{ route('association.requests.complete', $match) }}"
                      onsubmit="return confirm('Confirmer la réception de ce don ?')">
                    @csrf
                    <button class="rounded-md bg-emerald-600 px-3 py-1.5 font-medium text-white hover:bg-emerald-700">Marquer comme remis</button>
                </form>
            </div>
        </div>
    @empty
        <p class="mt-2 text-sm text-stone-500">Aucun don en attente de remise.</p>
    @endforelse
</section>
@endsection
