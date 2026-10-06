@extends('layouts.don')

@section('title', 'Profil de l\'association')

@section('content')
@php
    use App\Support\Textile;

    $input = 'mt-1 block w-full rounded-md border border-stone-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500';
    $label = 'block text-sm font-medium text-stone-700';
    $conditions = old('accepted_conditions', $association->accepted_conditions ?? []);
    $categories = old('accepted_categories', $association->accepted_categories ?? []);
@endphp

<h1 class="text-2xl font-semibold">Profil de l'association</h1>

<form novalidate method="POST" action="{{ route('association.profile.update') }}"
      class="mt-6 space-y-6 rounded-lg border border-stone-200 bg-white p-6">
    @csrf
    @method('PUT')

    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="{{ $label }}" for="name">Nom</label>
            <input id="name" name="name" required maxlength="150" class="{{ $input }}" value="{{ old('name', $association->name) }}">
            @include('don.partials.field-error', ['name' => 'name'])
        </div>

        <div class="sm:col-span-2">
            <label class="{{ $label }}" for="description">Présentation</label>
            <textarea id="description" name="description" rows="3" maxlength="2000" class="{{ $input }}">{{ old('description', $association->description) }}</textarea>
            @include('don.partials.field-error', ['name' => 'description'])
        </div>

        <div>
            <label class="{{ $label }}" for="city">Ville</label>
            <input id="city" name="city" required maxlength="100" class="{{ $input }}" value="{{ old('city', $association->city) }}">
            @include('don.partials.field-error', ['name' => 'city'])
        </div>

        <div>
            <label class="{{ $label }}" for="phone">Téléphone</label>
            <input id="phone" name="phone" maxlength="30" class="{{ $input }}" value="{{ old('phone', $association->phone) }}">
            @include('don.partials.field-error', ['name' => 'phone'])
        </div>

        <div>
            <label class="{{ $label }}" for="capacity">Capacité (pièces en attente de remise)</label>
            <input id="capacity" name="capacity" type="number" min="1" required class="{{ $input }}" value="{{ old('capacity', $association->capacity ?? 100) }}">
            @include('don.partials.field-error', ['name' => 'capacity'])
            <p class="mt-1 text-xs text-stone-400">Au-delà, l'association n'est plus proposée aux donateurs.</p>
        </div>

        <div>
            <label class="{{ $label }}" for="opening_hours">Horaires de dépôt</label>
            <input id="opening_hours" name="opening_hours" maxlength="500" class="{{ $input }}" placeholder="Lun-ven 9h-17h" value="{{ old('opening_hours', $association->opening_hours) }}">
            @include('don.partials.field-error', ['name' => 'opening_hours'])
        </div>
    </div>

    <fieldset>
        <legend class="text-sm font-semibold">États acceptés</legend>
        <p class="text-xs text-stone-500">Aucune case cochée = tous les états sont acceptés.</p>
        <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2">
            @foreach (Textile::CONDITIONS as $key => $text)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="accepted_conditions[]" value="{{ $key }}" @checked(in_array($key, $conditions, true))>
                    {{ $text }}
                </label>
            @endforeach
        </div>
    </fieldset>

    <fieldset>
        <legend class="text-sm font-semibold">Catégories acceptées</legend>
        <p class="text-xs text-stone-500">Aucune case cochée = toutes les catégories sont acceptées.</p>
        <div class="mt-2 grid gap-2 sm:grid-cols-2">
            @foreach (Textile::CATEGORIES as $key => $text)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="accepted_categories[]" value="{{ $key }}" @checked(in_array($key, $categories, true))>
                    {{ $text }}
                </label>
            @endforeach
        </div>
    </fieldset>

    <div class="flex justify-end">
        <button class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700">Enregistrer</button>
    </div>
</form>
@endsection
