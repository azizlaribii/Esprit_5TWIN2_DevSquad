@extends('layouts.don')

@section('title', 'Faire un don')

@section('content')
    <h1 class="text-2xl font-semibold">Faire un don</h1>
    <p class="mt-1 text-stone-600">Ajoutez des photos : notre IA reconnaît le type, la taille et l'état, puis vous propose les associations les plus adaptées.</p>

    <form method="POST" action="{{ route('donations.store') }}" enctype="multipart/form-data"
          class="mt-8 rounded-lg border border-stone-200 bg-white p-6">
        @csrf

        <div class="mb-8">
            <label class="block text-sm font-medium text-stone-700" for="photos">Photos (1 à {{ config('textilecycle.max_photos') }})</label>
            <input id="photos" name="photos[]" type="file" multiple required accept="image/jpeg,image/png,image/webp"
                   class="mt-1 block w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-emerald-700">
            <p class="mt-1 text-xs text-stone-400">JPG, PNG ou WebP, {{ round(config('textilecycle.max_photo_kb') / 1024) }} Mo maximum par photo. Photographiez l'étiquette de taille si possible.</p>
        </div>

        @include('don.partials.donation-fields', ['donation' => null, 'required' => false])

        <div class="mt-8 flex justify-end">
            <button class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Trouver une association
            </button>
        </div>
    </form>
@endsection
