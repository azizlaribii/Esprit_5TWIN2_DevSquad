@extends('layouts.app')

@section('title', 'Mes Dons Textiles')
@section('meta_description', 'Historique et suivi de vos dons de vêtements avec matching IA')
@section('breadcrumb', 'Gestion › Mes Dons')

@section('content')
<div class="animate-fade-in-up">

    {{-- Top Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Mes Dons Textiles</h1>
            <p class="page-subtitle">Suivez l'état de vos dons et les propositions des associations partenaires</p>
        </div>
        <div style="display:flex;gap:.75rem">
            <a href="{{ route('dons.index') }}" class="btn btn-secondary">
                <span class="material-icons-round">history</span> Historique global
            </a>
            <a href="{{ route('donations.create') }}" class="btn btn-primary">
                <span class="material-icons-round">add_circle</span> Faire un nouveau don
            </a>
        </div>
    </div>


    {{-- List of Donations --}}
    <div style="display:grid;gap:1rem">
        @forelse ($donations as $donation)
            @php
                use App\Models\Donation;
                $statusClass = match($donation->status) {
                    Donation::ACCEPTED, Donation::COMPLETED => 'badge-success',
                    Donation::MATCHED                      => 'badge-info',
                    Donation::PENDING_ANALYSIS             => 'badge-warning',
                    Donation::NEEDS_REVIEW                 => 'badge-warning',
                    Donation::REQUESTED                    => 'badge-info',
                    Donation::CANCELLED                    => 'badge-danger',
                    default                                => 'badge-secondary',
                };
            @endphp
            <div class="card" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.25rem;padding:1.25rem 1.5rem">
                <div style="display:flex;align-items:center;gap:1.25rem">
                    @if ($donation->photos->first())
                        <img src="{{ $donation->photos->first()->url() }}" alt="" style="width:68px;height:68px;border-radius:10px;object-fit:cover;border:1px solid var(--border)">
                    @else
                        <div style="width:68px;height:68px;border-radius:10px;background:rgba(255,255,255,.05);display:flex;align-items:center;justify-content:center;color:var(--text-muted)">
                            <span class="material-icons-round" style="font-size:1.75rem">checkroom</span>
                        </div>
                    @endif

                    <div>
                        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.25rem">
                            <h3 style="font-size:1.05rem;font-weight:700;color:var(--text-primary);margin:0">
                                {{ $donation->title }}
                            </h3>
                            <span class="badge {{ $statusClass }}" style="font-size:.7rem">
                                {{ $donation->statusLabel() }}
                            </span>
                        </div>
                        <p style="font-size:.82rem;color:var(--text-muted);margin:0">
                            {{ $donation->category ? $donation->categoryLabel() : 'Catégorie déduite par IA' }}
                            &nbsp;·&nbsp; {{ $donation->quantity }} pièce(s)
                            &nbsp;·&nbsp; {{ $donation->city }}
                            &nbsp;·&nbsp; Déposé le {{ $donation->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:.75rem">
                    <a href="{{ route('donations.show', $donation) }}" class="btn btn-secondary btn-sm" style="display:inline-flex;align-items:center;gap:.3rem">
                        <span class="material-icons-round" style="font-size:.95rem">visibility</span>
                        Voir les détails & suggestions
                    </a>
                </div>
            </div>
        @empty
            <div class="card" style="text-align:center;padding:3rem">
                <span class="material-icons-round" style="font-size:3rem;color:var(--text-muted);display:block;margin-bottom:1rem">volunteer_activism</span>
                <p style="color:var(--text-muted);margin-bottom:1.5rem">Vous n'avez pas encore enregistré de don intelligent.</p>
                <a href="{{ route('donations.create') }}" class="btn btn-primary">
                    <span class="material-icons-round">add_circle</span> Donner mes premiers vêtements
                </a>
            </div>
        @endforelse
    </div>

    @if($donations->hasPages())
        <div style="margin-top:1.5rem">
            {{ $donations->links() }}
        </div>
    @endif

</div>
@endsection
