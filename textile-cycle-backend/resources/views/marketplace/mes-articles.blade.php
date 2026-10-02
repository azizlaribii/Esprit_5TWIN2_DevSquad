@extends('layouts.app')

@section('title', 'Mes Articles')
@section('meta_description', 'Gérez vos annonces publiées sur la Marketplace TexTileCycle')
@section('breadcrumb', 'Marketplace › Mes Articles')

@section('styles')
<style>
/* ═══ STATS STRIP ═══ */
.stats-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem}
.stat-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;transition:var(--transition)}
.stat-card:hover{transform:translateY(-2px);border-color:var(--border-active);box-shadow:var(--shadow-glow)}
.stat-icon{width:46px;height:46px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stat-icon .material-icons-round{font-size:1.375rem;color:white}
.stat-value{font-family:'Outfit',sans-serif;font-size:1.75rem;font-weight:800;color:var(--text-primary);line-height:1}
.stat-label{font-size:.78rem;color:var(--text-muted);font-weight:500;margin-top:.2rem}
/* ═══ TOOLBAR ═══ */
.toolbar{display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap}
.toolbar-left{flex:1;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap}
/* ═══ TABLE ═══ */
.articles-table-wrap{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden}
.articles-table{width:100%;border-collapse:collapse;font-size:.875rem}
.articles-table thead th{padding:.875rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);background:rgba(255,255,255,.025);border-bottom:1px solid var(--border);white-space:nowrap}
.articles-table tbody tr{transition:background .15s}
.articles-table tbody tr:hover{background:rgba(108,99,255,.04)}
.articles-table tbody td{padding:.875rem 1rem;border-bottom:1px solid rgba(255,255,255,.03);vertical-align:middle}
.articles-table tbody tr:last-child td{border-bottom:none}
/* ═══ PRODUCT CELL ═══ */
.product-cell{display:flex;align-items:center;gap:.875rem}
.product-thumb{width:48px;height:48px;border-radius:var(--radius-sm);background:linear-gradient(135deg,rgba(108,99,255,.15),rgba(67,217,173,.08));border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;overflow:hidden}
.product-thumb img{width:100%;height:100%;object-fit:cover}
.product-name{font-weight:600;color:var(--text-primary);font-size:.875rem;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.product-sub{font-size:.75rem;color:var(--text-muted);margin-top:.15rem}
/* ═══ BADGES ═══ */
.status-pill{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;border-radius:100px;font-size:.72rem;font-weight:700;white-space:nowrap}
.status-disponible{background:rgba(67,217,173,.12);color:var(--secondary);border:1px solid rgba(67,217,173,.3)}
.status-en_cours{background:rgba(255,167,38,.12);color:var(--accent-orange);border:1px solid rgba(255,167,38,.3)}
.status-vendu{background:rgba(255,101,132,.12);color:var(--accent-red);border:1px solid rgba(255,101,132,.3)}
.type-pill{display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .55rem;border-radius:100px;font-size:.7rem;font-weight:700}
.type-vente{background:rgba(108,99,255,.12);color:var(--primary-light);border:1px solid rgba(108,99,255,.3)}
.type-echange{background:rgba(255,167,38,.12);color:var(--accent-orange);border:1px solid rgba(255,167,38,.3)}
.type-don{background:rgba(67,217,173,.12);color:var(--secondary);border:1px solid rgba(67,217,173,.3)}
/* ═══ ACTIONS ═══ */
.action-group{display:flex;align-items:center;gap:.4rem}
.btn-icon{width:34px;height:34px;border-radius:var(--radius-sm);border:1px solid var(--border);background:rgba(255,255,255,.04);color:var(--text-secondary);display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:var(--transition);text-decoration:none}
.btn-icon:hover{background:rgba(108,99,255,.15);border-color:var(--primary);color:var(--primary-light)}
.btn-icon.danger:hover{background:rgba(255,101,132,.15);border-color:var(--accent-red);color:var(--accent-red)}
.btn-icon .material-icons-round{font-size:1.1rem}
/* ═══ EMPTY STATE ═══ */
.empty-state{text-align:center;padding:4rem 2rem}
.empty-state .material-icons-round{font-size:4rem;color:var(--text-muted);display:block;margin-bottom:1rem}
.empty-state h3{font-size:1.25rem;color:var(--text-primary);margin-bottom:.5rem}
.empty-state p{color:var(--text-muted);font-size:.9rem;margin-bottom:1.5rem}
/* ═══ DELETE MODAL ═══ */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(6px);z-index:1000;display:none;align-items:center;justify-content:center}
.modal-overlay.open{display:flex}
.modal-box{background:var(--bg-card);border:1px solid rgba(255,101,132,.3);border-radius:var(--radius-lg);padding:2rem;max-width:420px;width:90%;animation:fadeInUp .25s ease forwards;text-align:center}
.modal-box .material-icons-round.warn-icon{font-size:3rem;color:var(--accent-red);margin-bottom:1rem;display:block}
.modal-box h3{font-size:1.125rem;font-weight:700;color:var(--text-primary);margin-bottom:.5rem}
.modal-box p{color:var(--text-secondary);font-size:.875rem;margin-bottom:1.5rem}
.modal-actions{display:flex;gap:.75rem;justify-content:center}
/* ═══ AI SCORE CHIP ═══ */
.ai-chip-sm{display:inline-flex;align-items:center;gap:.2rem;padding:.15rem .45rem;background:linear-gradient(135deg,rgba(108,99,255,.15),rgba(67,217,173,.08));border:1px solid rgba(108,99,255,.3);border-radius:100px;font-size:.68rem;font-weight:700;color:var(--primary-light)}
@media(max-width:1100px){.stats-strip{grid-template-columns:repeat(2,1fr)}}
@media(max-width:650px){.stats-strip{grid-template-columns:1fr}.toolbar{flex-direction:column;align-items:stretch}}
</style>
@endsection

@section('content')
<div class="animate-fade-in-up">

    {{-- PAGE HEADER --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Mes Articles</h1>
            <p class="page-subtitle">Gérez, modifiez ou supprimez vos annonces publiées</p>
        </div>
        <a href="{{ route('marketplace.create') }}" class="btn btn-primary" id="btn-new-article">
            <span class="material-icons-round">add_circle</span> Nouvel article
        </a>
    </div>

    {{-- STATS STRIP --}}
    <div class="stats-strip">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--gradient-primary)">
                <span class="material-icons-round">inventory_2</span>
            </div>
            <div>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Total publications</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#43D9AD,#29B6F6)">
                <span class="material-icons-round">check_circle</span>
            </div>
            <div>
                <div class="stat-value" style="color:var(--secondary)">{{ $stats['disponible'] }}</div>
                <div class="stat-label">Disponibles</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#FFA726,#FF7043)">
                <span class="material-icons-round">pending</span>
            </div>
            <div>
                <div class="stat-value" style="color:var(--accent-orange)">{{ $stats['en_cours'] }}</div>
                <div class="stat-label">En cours</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#FF6584,#FF8E53)">
                <span class="material-icons-round">sell</span>
            </div>
            <div>
                <div class="stat-value" style="color:var(--accent-red)">{{ $stats['vendu'] }}</div>
                <div class="stat-label">Vendus</div>
            </div>
        </div>
    </div>

    {{-- TOOLBAR --}}
    <form method="GET" action="{{ route('marketplace.mes-articles') }}" id="filter-form">
        <div class="toolbar">
            <div class="toolbar-left">
                <select name="statut" class="form-control" style="width:160px" onchange="this.form.submit()" id="filter-statut">
                    <option value="">Tous les statuts</option>
                    <option value="disponible" {{ request('statut') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                    <option value="en_cours"   {{ request('statut') == 'en_cours'   ? 'selected' : '' }}>En cours</option>
                    <option value="vendu"      {{ request('statut') == 'vendu'      ? 'selected' : '' }}>Vendu</option>
                </select>
                <select name="type" class="form-control" style="width:160px" onchange="this.form.submit()" id="filter-type">
                    <option value="">Tous les types</option>
                    <option value="vente"   {{ request('type') == 'vente'   ? 'selected' : '' }}>Vente</option>
                    <option value="echange" {{ request('type') == 'echange' ? 'selected' : '' }}>Echange</option>
                </select>
                @if(request()->hasAny(['statut','type']))
                    <a href="{{ route('marketplace.mes-articles') }}" class="btn btn-secondary btn-sm" id="btn-reset-filters">
                        <span class="material-icons-round" style="font-size:.95rem">close</span> Reinitialiser
                    </a>
                @endif
            </div>
            <div style="color:var(--text-secondary);font-size:.85rem;white-space:nowrap">
                <strong style="color:var(--text-primary)">{{ $articles->total() }}</strong> article{{ $articles->total() > 1 ? 's' : '' }}
            </div>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="articles-table-wrap">
        @if($articles->isEmpty())
            <div class="empty-state">
                <span class="material-icons-round">store</span>
                <h3>Aucun article publie</h3>
                <p>Vous n'avez pas encore publie d'article sur la marketplace.<br>Commencez a vendre ou echanger vos vetements !</p>
                <a href="{{ route('marketplace.create') }}" class="btn btn-primary" id="btn-first-article">
                    <span class="material-icons-round">add_circle</span> Publier mon premier article
                </a>
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="articles-table" id="articles-table">
                    <thead>
                        <tr>
                            <th style="width:260px">Article</th>
                            <th>Categorie</th>
                            <th>Type</th>
                            <th>Prix</th>
                            <th>Etat</th>
                            <th>Statut</th>
                            <th>Score IA</th>
                            <th>Vues</th>
                            <th>Date</th>
                            <th style="text-align:center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($articles as $article)
                        <tr id="row-{{ $article->id }}">
                            <td>
                                <div class="product-cell">
                                    <div class="product-thumb">
                                        @if($article->image_url)
                                            <img src="{{ asset('storage/'.$article->image_url) }}" alt="{{ $article->titre }}">
                                        @else
                                            {{ ['👕','👗','👖','🧥','👟','🎽','🧢'][($article->id ?? 0) % 7] }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="product-name" title="{{ $article->titre }}">{{ $article->titre }}</div>
                                        <div class="product-sub">{{ $article->marque ?? 'Sans marque' }} &bull; T.{{ $article->taille }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="color:var(--text-secondary);font-size:.82rem">{{ $article->categorie }}</td>
                            @php
                                $effectiveType = ($article->type === 'don') ? 'vente' : $article->type;
                                $displayPrice = $article->prix ?? $article->ai_prix_min ?? 15.00;
                            @endphp
                            <td>
                                <span class="type-pill type-{{ $effectiveType }}">
                                    {{ $effectiveType == 'vente' ? '💰' : '🔄' }}
                                    {{ ucfirst($effectiveType) }}
                                </span>
                            </td>
                            <td>
                                @if($effectiveType == 'echange')
                                    <span style="color:var(--accent-orange);font-weight:700;font-size:.85rem">Echange</span>
                                @else
                                    <span style="font-family:'Outfit',sans-serif;font-weight:800;color:var(--primary-light)">
                                        {{ number_format($displayPrice, 2).' DT' }}
                                    </span>
                                @endif
                            </td>
                            <td style="font-size:.8rem;color:var(--text-secondary)">{{ $article->etat }}</td>
                            <td>
                                <span class="status-pill status-{{ $article->statut }}">
                                    @if($article->statut == 'disponible') ✅ Disponible
                                    @elseif($article->statut == 'en_cours') ⏳ En cours
                                    @else 💰 Vendu @endif
                                </span>
                            </td>
                            <td>
                                @if($article->ai_score)
                                    <span class="ai-chip-sm">✦ {{ $article->ai_score }}%</span>
                                @else
                                    <span style="color:var(--text-muted);font-size:.78rem">-</span>
                                @endif
                            </td>
                            <td>
                                <span style="display:flex;align-items:center;gap:.3rem;color:var(--text-muted);font-size:.82rem">
                                    <span class="material-icons-round" style="font-size:.9rem">visibility</span>
                                    {{ $article->vues ?? 0 }}
                                </span>
                            </td>
                            <td style="color:var(--text-muted);font-size:.8rem;white-space:nowrap">
                                {{ $article->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="action-group" style="justify-content:center">
                                    <a href="{{ route('marketplace.show', $article) }}"
                                       class="btn-icon"
                                       title="Voir l'annonce"
                                       id="btn-view-{{ $article->id }}">
                                        <span class="material-icons-round">visibility</span>
                                    </a>
                                    <a href="{{ route('marketplace.edit', $article) }}"
                                       class="btn-icon"
                                       title="Modifier"
                                       id="btn-edit-{{ $article->id }}">
                                        <span class="material-icons-round">edit</span>
                                    </a>
                                    <button type="button"
                                            class="btn-icon danger"
                                            title="Supprimer"
                                            id="btn-delete-{{ $article->id }}"
                                            onclick="openDeleteModal({{ $article->id }}, '{{ addslashes($article->titre) }}')">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($articles->hasPages())
                <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;justify-content:center">
                    {{ $articles->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- QUICK LINKS --}}
    <div style="display:flex;gap:.75rem;margin-top:1.25rem;flex-wrap:wrap">
        <a href="{{ route('marketplace.index') }}" class="btn btn-secondary btn-sm">
            <span class="material-icons-round" style="font-size:1rem">storefront</span> Voir la marketplace
        </a>
        <a href="{{ route('marketplace.favoris') }}" class="btn btn-secondary btn-sm">
            <span class="material-icons-round" style="font-size:1rem">favorite</span> Mes Favoris
        </a>
    </div>

</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div class="modal-overlay" id="delete-modal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <span class="material-icons-round warn-icon">warning_amber</span>
        <h3>Supprimer cet article ?</h3>
        <p id="delete-modal-text">Cette action est irreversible. L'annonce sera definitivement supprimee.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" id="btn-cancel-delete" onclick="closeDeleteModal()">
                Annuler
            </button>
            <form id="delete-form" method="POST" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" id="btn-confirm-delete">
                    <span class="material-icons-round" style="font-size:1rem">delete</span> Supprimer
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openDeleteModal(articleId, titre) {
    document.getElementById('delete-modal-text').textContent =
        'Vous etes sur le point de supprimer ' + titre + '. Cette action est irreversible.';
    document.getElementById('delete-form').action = '/marketplace/' + articleId;
    document.getElementById('delete-modal').classList.add('open');
}
function closeDeleteModal() {
    document.getElementById('delete-modal').classList.remove('open');
}
document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeleteModal();
});
</script>
@endsection

