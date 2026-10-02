@extends('layouts.don')

@section('title', 'Vérification des associations')

@section('content')
    <h1 class="text-2xl font-semibold">Associations</h1>
    <p class="mt-1 text-sm text-stone-600">Seules les associations vérifiées sont proposées aux donateurs.</p>

    <section class="mt-6 overflow-x-auto rounded-lg border border-stone-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-stone-50 text-xs uppercase text-stone-500">
                <tr>
                    <th class="px-4 py-2">Association</th><th class="px-4 py-2">Ville</th>
                    <th class="px-4 py-2">Compte</th><th class="px-4 py-2">Inscrite le</th>
                    <th class="px-4 py-2">Statut</th><th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($associations as $association)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $association->name }}</td>
                        <td class="px-4 py-2">{{ $association->city }}</td>
                        <td class="px-4 py-2 text-stone-500">{{ $association->user->email }}</td>
                        <td class="px-4 py-2 text-stone-500">{{ $association->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">
                            @include('don.partials.status-badge', [
                                'status' => $association->isVerified() ? 'accepted' : 'pending_analysis',
                                'label'  => $association->isVerified() ? 'Vérifiée' : 'À vérifier',
                            ])
                        </td>
                        <td class="px-4 py-2 text-right">
                            <form method="POST" action="{{ route('admin.associations.toggle', $association) }}">
                                @csrf
                                <button class="{{ $association->isVerified() ? 'text-red-600' : 'text-emerald-700' }} hover:underline">
                                    {{ $association->isVerified() ? 'Retirer la vérification' : 'Vérifier' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-stone-500">Aucune association inscrite.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
