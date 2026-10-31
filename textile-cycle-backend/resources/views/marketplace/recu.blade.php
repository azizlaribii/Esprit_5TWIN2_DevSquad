@extends('layouts.app')

@section('title', 'Reçu de Transaction Circulaire #' . $article->id)
@section('meta_description', 'Preuve d\'achat et certificat d\'impact environnemental pour votre article sur TexTileCycle')
@section('breadcrumb', 'Marketplace › Reçu #' . $article->id)

@section('styles')
<style>
.receipt-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: 0 10px 30px rgba(0,0,0,.05);
    max-width: 760px;
    margin: 0 auto 2rem;
    overflow: hidden;
}
.receipt-header {
    background: linear-gradient(135deg, rgba(108,99,255,.08) 0%, rgba(67,217,173,.08) 100%);
    border-bottom: 1px solid var(--border);
    padding: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}
.receipt-body {
    padding: 2rem;
}
.receipt-grid {
    display: grid;
    grid-template-columns: 120px 1fr;
    gap: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
    margin-bottom: 1.5rem;
    align-items: center;
}
.eco-impact-box {
    background: rgba(67,217,173,.08);
    border: 1px solid rgba(67,217,173,.3);
    border-radius: var(--radius-md);
    padding: 1.25rem;
    margin-top: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-around;
    text-align: center;
    flex-wrap: wrap;
    gap: 1rem;
}
@media print {
    .sidebar, .topbar, .btn-print, .btn-back {
        display: none !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .receipt-card {
        box-shadow: none !important;
        border: none !important;
        max-width: 100% !important;
    }
}
</style>
@endsection

@section('content')
<div class="animate-fade-in-up">

    <div style="max-width:760px;margin:0 auto 1.25rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem">
        <a href="{{ route('marketplace.mes-articles', ['tab' => 'achats']) }}" class="btn btn-secondary btn-sm btn-back">
            <span class="material-icons-round" style="font-size:1rem">arrow_back</span> Retour à mes articles
        </a>
        <div style="display:flex;gap:.75rem">
            <button onclick="window.print()" class="btn btn-primary btn-sm btn-print">
                <span class="material-icons-round" style="font-size:1rem">print</span> Imprimer le reçu
            </button>
        </div>
    </div>

    <div class="receipt-card">
        {{-- Header --}}
        <div class="receipt-header">
            <div>
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem">
                    <span class="material-icons-round" style="color:var(--primary);font-size:1.8rem">recycling</span>
                    <strong style="font-family:'Outfit',sans-serif;font-size:1.35rem;color:var(--text-primary)">TexTileCycle</strong>
                </div>
                <div style="font-size:.82rem;color:var(--text-secondary)">Plateforme de Mode Circulaire & Éco-Responsable</div>
            </div>
            <div style="text-align:right">
                <span class="badge badge-success" style="font-size:.78rem;padding:.3rem .75rem">Transaction validée</span>
                <div style="font-size:.8rem;color:var(--text-muted);margin-top:.4rem">N° TTC-{{ str_pad($article->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div style="font-size:.78rem;color:var(--text-muted)">Date : {{ $article->updated_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        {{-- Body --}}
        <div class="receipt-body">
            {{-- Product Summary --}}
            <div class="receipt-grid">
                <div style="width:120px;height:120px;border-radius:var(--radius-md);overflow:hidden;border:1px solid var(--border)">
                    <img src="{{ $article->display_image_url }}" alt="{{ $article->titre }}" style="width:100%;height:100%;object-fit:cover" onerror="this.src='/images/default-clothing.jpg'">
                </div>
                <div>
                    <h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary);margin-bottom:.35rem">{{ $article->titre }}</h2>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.5rem">
                        <span class="badge badge-primary" style="font-size:.72rem">{{ $article->categorie }}</span>
                        @if($article->marque) <span class="badge badge-secondary" style="font-size:.72rem">{{ $article->marque }}</span> @endif
                        <span class="badge badge-info" style="font-size:.72rem">Taille {{ $article->taille }}</span>
                        <span class="badge badge-success" style="font-size:.72rem">{{ $article->etat }}</span>
                    </div>
                    <div style="font-size:.85rem;color:var(--text-secondary)">
                        Mode : <strong>{{ ucfirst($article->type) }}</strong> &bull; Genre : {{ $article->genre }}
                    </div>
                </div>
            </div>

            {{-- Participants --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid var(--border)">
                <div>
                    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:.35rem">Vendeur</div>
                    <div style="font-weight:700;color:var(--text-primary)">{{ $article->vendeur->name ?? 'Membre TexTileCycle' }}</div>
                    <div style="font-size:.8rem;color:var(--text-secondary)">{{ $article->vendeur->email ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:.35rem">Acheteur</div>
                    <div style="font-weight:700;color:var(--text-primary)">{{ $article->acheteur->name ?? auth()->user()->name ?? 'Membre' }}</div>
                    <div style="font-size:.8rem;color:var(--text-secondary)">{{ $article->acheteur->email ?? auth()->user()->email ?? '-' }}</div>
                </div>
            </div>

            {{-- Financial Summary --}}
            <div style="padding-top:1.5rem">
                <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;font-size:.9rem;color:var(--text-secondary)">
                    <span>Montant de l'article :</span>
                    <strong>{{ $article->prix ? number_format($article->prix, 2) . ' DT' : 'Échange textile' }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;font-size:.9rem;color:var(--text-secondary)">
                    <span>Frais de service éco-plateforme :</span>
                    <strong style="color:var(--secondary)">0.00 DT (Offert)</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding-top:.75rem;border-top:1px solid var(--border);font-size:1.15rem;font-weight:800;color:var(--text-primary)">
                    <span>Total Réglé :</span>
                    <span style="color:var(--primary)">{{ $article->prix ? number_format($article->prix, 2) . ' DT' : '0.00 DT' }}</span>
                </div>
            </div>

            {{-- Eco impact certificate --}}
            <div class="eco-impact-box">
                <div>
                    <div style="font-size:1.4rem;font-weight:800;color:var(--secondary)">~2.8 kg</div>
                    <div style="font-size:.72rem;color:var(--text-secondary);font-weight:600">CO2e Évités</div>
                </div>
                <div style="width:1px;height:35px;background:var(--border)"></div>
                <div>
                    <div style="font-size:1.4rem;font-weight:800;color:var(--primary)">~2 500 L</div>
                    <div style="font-size:.72rem;color:var(--text-secondary);font-weight:600">Eau Économisée</div>
                </div>
                <div style="width:1px;height:35px;background:var(--border)"></div>
                <div>
                    <div style="font-size:1.4rem;font-weight:800;color:var(--accent-orange)">+15 pts</div>
                    <div style="font-size:.72rem;color:var(--text-secondary);font-weight:600">Éco-Score Profil</div>
                </div>
            </div>

            <div style="text-align:center;margin-top:1.75rem;font-size:.75rem;color:var(--text-muted)">
                Ce document certifie la réutilisation de cette pièce textile au sein du réseau d'économie circulaire TexTileCycle.
            </div>
        </div>
    </div>

</div>
@endsection

