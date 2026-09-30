@extends('layouts.don')

@section('title', 'Modifier le don')

@section('content')
    <h1 class="text-2xl font-semibold">Modifier le don</h1>

    @if ($donation->status === \App\Models\Donation::NEEDS_REVIEW)
        <div class="mt-4 rounded-md border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800">
            L'analyse automatique n'a pas pu déterminer toutes les caractéristiques. Complétez-les pour que nous puissions trouver une association.
        </div>
    @endif

    <form method="POST" action="{{ route('donations.update', $donation) }}"
          class="mt-6 rounded-lg border border-stone-200 bg-white p-6">
        @csrf
        @method('PUT')

        @include('don.partials.donation-fields', [
            'donation' => $donation,
            'required' => $donation->status === \App\Models\Donation::NEEDS_REVIEW,
        ])

        <div class="mt-8 flex items-center justify-between">
            <a href="{{ route('donations.show', $donation) }}" class="text-sm text-stone-500 hover:underline">Annuler</a>
            <button class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Enregistrer et relancer la recherche
            </button>
        </div>
    </form>
@endsection
