@extends('layouts.don')

@section('title', 'Demande de don')

@section('content')
@php
    use App\Models\DonationMatch;
    use App\Support\Textile;

    $donation = $match->donation;
    $revealed = in_array($match->status, [DonationMatch::ACCEPTED, DonationMatch::COMPLETED], true);
    $pending  = $match->status === DonationMatch::REQUESTED && $donation->status === \App\Models\Donation::REQUESTED;
    $input = 'mt-1 block w-full rounded-md border border-stone-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500';
@endphp

<a href="{{ route('association.dashboard') }}" class="text-sm text-stone-500 hover:underline">← Tableau de bord</a>

<div class="mt-2 flex flex-wrap items-start justify-between gap-3">
    <h1 class="text-2xl font-semibold">{{ $donation->quantity }} × {{ Textile::label('categories', $donation->category) }}</h1>
    @include('don.partials.status-badge', ['status' => $match->status, 'label' => $match->statusLabel()])
</div>

<section class="mt-6 grid gap-6 rounded-lg border border-stone-200 bg-white p-5 md:grid-cols-2">
    <div class="grid grid-cols-3 gap-2">
        @foreach ($donation->photos as $photo)
            <img src="{{ $photo->url() }}" alt="Photo du don" class="aspect-square w-full rounded-md object-cover">
        @endforeach
    </div>

    <div>
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
            <dt class="text-stone-500">État</dt><dd>{{ Textile::label('conditions', $donation->condition) }}</dd>
            <dt class="text-stone-500">Public</dt><dd>{{ Textile::label('age_groups', $donation->age_group) }}</dd>
            <dt class="text-stone-500">Taille</dt><dd>{{ $donation->size ?: '—' }}</dd>
            <dt class="text-stone-500">Genre</dt><dd>{{ Textile::label('genders', $donation->gender) }}</dd>
            <dt class="text-stone-500">Saison</dt><dd>{{ Textile::label('seasons', $donation->season) }}</dd>
            <dt class="text-stone-500">Ville du donateur</dt><dd>{{ $donation->city }}</dd>
        </dl>

        @if ($donation->description)
            <p class="mt-4 text-sm text-stone-600">{!! nl2br(e($donation->description)) !!}</p>
        @endif

        <div class="mt-4 rounded-md bg-stone-50 p-3 text-xs text-stone-600">
            <p class="font-medium text-stone-700">Pourquoi cette demande vous est adressée</p>
            <p class="mt-1">{{ $match->displayExplanation() }}</p>
        </div>
    </div>
</section>

@if ($revealed)
    <section class="mt-6 rounded-lg border border-green-200 bg-green-50 p-5 text-sm text-green-900">
        <p class="font-medium">Coordonnées du donateur</p>
        <dl class="mt-2 grid grid-cols-[8rem_1fr] gap-y-1">
            <dt class="text-green-700">Nom</dt><dd>{{ $donation->user->name }}</dd>
            <dt class="text-green-700">E-mail</dt><dd>{{ $donation->user->email }}</dd>
            @if ($donation->user->phone)
                <dt class="text-green-700">Téléphone</dt><dd>{{ $donation->user->phone }}</dd>
            @endif
            <dt class="text-green-700">Rendez-vous</dt>
            <dd>{{ Textile::MEETING_TYPES[$match->meeting_type] ?? '—' }} · {{ $match->meeting_at?->format('d/m/Y à H:i') }}</dd>
            @if ($match->meeting_note)
                <dt class="text-green-700">Précisions</dt><dd>{{ $match->meeting_note }}</dd>
            @endif
        </dl>

        @if ($match->status === DonationMatch::ACCEPTED)
            <form method="POST" action="{{ route('association.requests.complete', $match) }}" class="mt-4"
                  onsubmit="return confirm('Confirmer la réception de ce don ?')">
                @csrf
                <button class="rounded-md bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">Marquer comme remis</button>
            </form>
        @endif
    </section>
@endif

@if ($pending)
    <section class="mt-6 rounded-lg border border-stone-200 bg-white p-6">
        <h2 class="text-lg font-semibold">Répondre à la demande</h2>

        <form novalidate method="POST" action="{{ route('association.requests.accept', $match) }}" class="mt-4 grid gap-5 sm:grid-cols-2">
            @csrf

            <div>
                <label class="block text-sm font-medium text-stone-700" for="meeting_type">Modalité</label>
                <select id="meeting_type" name="meeting_type" required class="{{ $input }}">
                    @foreach (Textile::MEETING_TYPES as $key => $text)
                        <option value="{{ $key }}" @selected(old('meeting_type') === $key)>{{ $text }}</option>
                    @endforeach
                </select>
                @include('don.partials.field-error', ['name' => 'meeting_type'])
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700" for="meeting_at">Date et heure</label>
                <input id="meeting_at" name="meeting_at" type="datetime-local" required class="{{ $input }}" value="{{ old('meeting_at') }}">
                @include('don.partials.field-error', ['name' => 'meeting_at'])
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-stone-700" for="meeting_note">Précisions <span class="font-normal text-stone-400">(adresse de dépôt, consignes…)</span></label>
                <textarea id="meeting_note" name="meeting_note" rows="2" maxlength="500" class="{{ $input }}">{{ old('meeting_note') }}</textarea>
                @include('don.partials.field-error', ['name' => 'meeting_note'])
            </div>

            <div class="flex items-center gap-3 sm:col-span-2">
                <button class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700">Accepter le don</button>
            </div>
        </form>

        <form method="POST" action="{{ route('association.requests.reject', $match) }}" class="mt-3"
              onsubmit="return confirm('Décliner cette demande ? Le donateur sera orienté vers une autre association.')">
            @csrf
            <button class="text-sm text-red-600 hover:underline">Décliner</button>
        </form>
    </section>
@endif
@endsection
