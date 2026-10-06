@extends('layouts.don')

@section('title', 'Nos besoins')

@section('content')
@php
    use App\Support\Textile;

    $input = 'mt-1 block w-full rounded-md border border-stone-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500';
    $label = 'block text-sm font-medium text-stone-700';
    $urgencies = [1 => '1 · Peu urgent', 2 => '2', 3 => '3 · Normal', 4 => '4', 5 => '5 · Critique'];
@endphp

<h1 class="text-2xl font-semibold">Nos besoins en vêtements</h1>
<p class="mt-1 text-sm text-stone-600">Plus vos besoins sont précis et à jour, mieux les dons vous parviennent. Un besoin diminue automatiquement à chaque don reçu.</p>

<section class="mt-6 overflow-x-auto rounded-lg border border-stone-200 bg-white">
    <table class="w-full text-left text-sm">
        <thead class="bg-stone-50 text-xs uppercase text-stone-500">
            <tr>
                <th class="px-4 py-2">Catégorie</th><th class="px-4 py-2">Public</th><th class="px-4 py-2">Taille</th>
                <th class="px-4 py-2">Genre</th><th class="px-4 py-2">Saison</th><th class="px-4 py-2">Quantité</th>
                <th class="px-4 py-2">Urgence</th><th class="px-4 py-2">Jusqu'au</th><th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse ($needs as $need)
                <tr class="{{ $need->quantity_needed === 0 ? 'text-stone-400' : '' }}">
                    <td class="px-4 py-2">{{ Textile::label('categories', $need->category) }}</td>
                    <td class="px-4 py-2">{{ $need->age_group ? Textile::label('age_groups', $need->age_group) : 'Tous' }}</td>
                    <td class="px-4 py-2">{{ $need->size ?: 'Toutes' }}</td>
                    <td class="px-4 py-2">{{ $need->gender ? Textile::label('genders', $need->gender) : 'Tous' }}</td>
                    <td class="px-4 py-2">{{ $need->season ? Textile::label('seasons', $need->season) : 'Toutes' }}</td>
                    <td class="px-4 py-2">{{ $need->quantity_needed }}</td>
                    <td class="px-4 py-2">{{ $need->urgency }}/5</td>
                    <td class="px-4 py-2">{{ $need->expires_at?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-4 py-2 text-right">
                        <form method="POST" action="{{ route('association.needs.destroy', $need) }}" onsubmit="return confirm('Supprimer ce besoin ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-4 py-6 text-center text-stone-500">Aucun besoin déclaré.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

<section class="mt-8 rounded-lg border border-stone-200 bg-white p-6">
    <h2 class="text-lg font-semibold">Ajouter un besoin</h2>

    <form novalidate method="POST" action="{{ route('association.needs.store') }}" class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @csrf

        <div>
            <label class="{{ $label }}" for="category">Catégorie</label>
            <select id="category" name="category" required class="{{ $input }}">
                <option value="">— Choisir —</option>
                @foreach (Textile::CATEGORIES as $key => $text)
                    <option value="{{ $key }}" @selected(old('category') === $key)>{{ $text }}</option>
                @endforeach
            </select>
            @include('don.partials.field-error', ['name' => 'category'])
        </div>

        <div>
            <label class="{{ $label }}" for="age_group">Public</label>
            <select id="age_group" name="age_group" class="{{ $input }}">
                <option value="">Tous</option>
                @foreach (Textile::AGE_GROUPS as $key => $text)
                    <option value="{{ $key }}" @selected(old('age_group') === $key)>{{ $text }}</option>
                @endforeach
            </select>
            @include('don.partials.field-error', ['name' => 'age_group'])
        </div>

        <div>
            <label class="{{ $label }}" for="size">Taille</label>
            <input id="size" name="size" list="sizes" maxlength="20" class="{{ $input }}" placeholder="Toutes" value="{{ old('size') }}">
            @include('don.partials.field-error', ['name' => 'size'])
            <datalist id="sizes">
                @foreach (Textile::SIZE_ORDER as $size)<option value="{{ $size }}">@endforeach
            </datalist>
        </div>

        <div>
            <label class="{{ $label }}" for="gender">Genre</label>
            <select id="gender" name="gender" class="{{ $input }}">
                <option value="">Tous</option>
                @foreach (Textile::GENDERS as $key => $text)
                    <option value="{{ $key }}" @selected(old('gender') === $key)>{{ $text }}</option>
                @endforeach
            </select>
            @include('don.partials.field-error', ['name' => 'gender'])
        </div>

        <div>
            <label class="{{ $label }}" for="season">Saison</label>
            <select id="season" name="season" class="{{ $input }}">
                <option value="">Toutes</option>
                @foreach (Textile::SEASONS as $key => $text)
                    <option value="{{ $key }}" @selected(old('season') === $key)>{{ $text }}</option>
                @endforeach
            </select>
            @include('don.partials.field-error', ['name' => 'season'])
        </div>

        <div>
            <label class="{{ $label }}" for="quantity_needed">Quantité recherchée</label>
            <input id="quantity_needed" name="quantity_needed" type="number" min="1" required class="{{ $input }}" value="{{ old('quantity_needed', 10) }}">
            @include('don.partials.field-error', ['name' => 'quantity_needed'])
        </div>

        <div>
            <label class="{{ $label }}" for="urgency">Urgence</label>
            <select id="urgency" name="urgency" class="{{ $input }}">
                @foreach ($urgencies as $key => $text)
                    <option value="{{ $key }}" @selected((int) old('urgency', 3) === $key)>{{ $text }}</option>
                @endforeach
            </select>
            @include('don.partials.field-error', ['name' => 'urgency'])
        </div>

        <div>
            <label class="{{ $label }}" for="expires_at">Valable jusqu'au <span class="font-normal text-stone-400">(facultatif)</span></label>
            <input id="expires_at" name="expires_at" type="date" class="{{ $input }}" value="{{ old('expires_at') }}">
            @include('don.partials.field-error', ['name' => 'expires_at'])
        </div>

        <div class="flex items-end">
            <button class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700">Ajouter</button>
        </div>
    </form>
</section>
@endsection
