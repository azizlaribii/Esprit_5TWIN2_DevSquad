@extends('layouts.don')

@section('title', 'Mes dons')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Mes dons</h1>
        <a href="{{ route('donations.create') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Faire un don</a>
    </div>

    @forelse ($donations as $donation)
        <a href="{{ route('donations.show', $donation) }}"
           class="mb-3 flex items-center gap-4 rounded-lg border border-stone-200 bg-white p-4 hover:border-emerald-300">
            @if ($donation->photos->first())
                <img src="{{ $donation->photos->first()->url() }}" alt="" class="h-16 w-16 rounded-md object-cover">
            @else
                <div class="h-16 w-16 rounded-md bg-stone-100"></div>
            @endif

            <div class="min-w-0 flex-1">
                <p class="truncate font-medium">{{ $donation->title }}</p>
                <p class="text-sm text-stone-500">
                    {{ $donation->category ? $donation->categoryLabel() : 'Catégorie à définir' }}
                    · {{ $donation->quantity }} pièce(s) · {{ $donation->created_at->format('d/m/Y') }}
                </p>
            </div>

            @include('don.partials.status-badge', ['status' => $donation->status, 'label' => $donation->statusLabel()])
        </a>
    @empty
        <div class="rounded-lg border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-stone-600">Vous n'avez pas encore fait de don.</p>
            <a href="{{ route('donations.create') }}" class="mt-3 inline-block text-emerald-700 hover:underline">Donner mes premiers vêtements</a>
        </div>
    @endforelse

    <div class="mt-6">{{ $donations->links() }}</div>
@endsection
