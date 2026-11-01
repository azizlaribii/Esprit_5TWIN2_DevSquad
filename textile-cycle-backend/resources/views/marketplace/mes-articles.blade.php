@extends('layouts.app')

@section('title', 'Mes Articles & Achats')
@section('meta_description', 'Gérez vos annonces publiées et consultez vos articles achetés sur la Marketplace TexTileCycle')
@section('breadcrumb', 'Marketplace › Mes Articles')

@section('styles')
<style>
/* ═══ TABS NAVIGATION ═══ */
.tabs-nav{display:flex;align-items:center;gap:.5rem;margin-bottom:1.5rem;border-bottom:1px solid var(--border);padding-bottom:.5rem}
.tab-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.25rem;border-radius:var(--radius-md);font-size:.9rem;font-weight:600;color:var(--text-secondary);background:transparent;border:1px solid transparent;cursor:pointer;transition:var(--transition);text-decoration:none}
.tab-btn:hover{color:var(--text-primary);background:rgba(255,255,255,.04)}
.tab-btn.active{color:var(--primary-light);background:rgba(108,99,255,.12);border-color:rgba(108,99,255,.3)}
.tab-btn .badge-count{font-size:.72rem;padding:.15rem .5rem;border-radius:100px;font-weight:700}
.tab-btn.active .badge-count{background:var(--primary);color:white}
.tab-btn:not(.active) .badge-count{background:rgba(255,255,255,.1);color:var(--text-muted)}

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

/* ═══ TABLE WRAPPER ═══ */
.articles-table-wrap{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;position:relative}
.table-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch}
.articles-table{width:100%;border-collapse:collapse;font-size:.875rem;min-width:720px}

/* ═══ TABLE HEAD ═══ */
.articles-table thead th{padding:.85rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);background:#F8FAFC;border-bottom:1px solid var(--border);white-space:nowrap}

/* ═══ TABLE BODY ═══ */
.articles-table tbody tr{transition:background .15s}
.articles-table tbody tr:hover{background:#F8FAFC}
.articles-table tbody td{padding:.85rem 1rem;border-bottom:1px solid #F1F5F9;vertical-align:middle;white-space:nowrap}
.articles-table tbody tr:last-child td{border-bottom:none}

/* ═══ SOLD / BOUGHT ROWS ═══ */
.articles-table tbody tr.row-vendu{background:#FFF5F5}
.articles-table tbody tr.row-vendu:hover{background:#FEE2E2}
.articles-table tbody tr.row-achete{background:#F0FDF4}
.articles-table tbody tr.row-achete:hover{background:#DCFCE7}

/* ═══ STICKY ACTIONS COLUMN ═══ */
.articles-table th.col-actions,.articles-table td.col-actions{position:sticky;right:0;z-index:2;background:#FFFFFF;border-left:1px solid var(--border);text-align:center;min-width:112px;box-shadow:-4px 0 8px rgba(0,0,0,0.02)}
.articles-table thead th.col-actions{background:#F8FAFC;color:var(--text-muted)}
.articles-table tbody tr:hover td.col-actions{background:#F8FAFC}
.articles-table tbody tr.row-vendu td.col-actions{background:#FFF5F5}
.articles-table tbody tr.row-vendu:hover td.col-actions{background:#FEE2E2}
.articles-table tbody tr.row-achete td.col-actions{background:#F0FDF4}
.articles-table tbody tr.row-achete:hover td.col-actions{background:#DCFCE7}

/* ═══ PRODUCT CELL ═══ */
.product-cell{display:flex;align-items:center;gap:.75rem;min-width:180px;max-width:240px}
.product-thumb{width:46px;height:46px;border-radius:var(--radius-sm);background:linear-gradient(135deg,rgba(108,99,255,.15),rgba(67,217,173,.08));border:1px solid var(--border);flex-shrink:0;overflow:hidden;display:flex;align-items:center;justify-content:center}
.product-thumb img{width:100%;height:100%;object-fit:cover}
.product-name{font-weight:600;color:var(--text-primary);font-size:.85rem;max-width:170px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.product-sub{font-size:.72rem;color:var(--text-muted);margin-top:.1rem}

/* ═══ STATUS CELL ═══ */
.status-cell{display:flex;flex-direction:column;gap:.3rem;align-items:flex-start}
.status-pill{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .6rem;border-radius:100px;font-size:.70rem;font-weight:700;white-space:nowrap}
.status-disponible{background:rgba(67,217,173,.12);color:var(--secondary);border:1px solid rgba(67,217,173,.3)}
.status-en_cours{background:rgba(255,167,38,.12);color:var(--accent-orange);border:1px solid rgba(255,167,38,.3)}
.status-vendu{background:rgba(255,101,132,.12);color:var(--accent-red);border:1px solid rgba(255,101,132,.3)}
.status-achete{background:rgba(67,217,173,.15);color:var(--secondary);border:1px solid rgba(67,217,173,.4)}

/* ═══ USER INLINE (Acheteur / Vendeur) ═══ */
.user-inline{display:flex;align-items:center;gap:.35rem;margin-top:.15rem;max-width:180px}
.user-avatar{width:20px;height:20px;border-radius:50%;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.user-avatar .material-icons-round{font-size:.78rem;color:white}
.user-info{overflow:hidden}
.user-name{font-size:.75rem;font-weight:600;color:var(--secondary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.user-sub{font-size:.65rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

/* ═══ TYPE PILLS ═══ */
.type-pill{display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .55rem;border-radius:100px;font-size:.7rem;font-weight:700}
.type-vente{background:rgba(108,99,255,.12);color:var(--primary-light);border:1px solid rgba(108,99,255,.3)}
.type-echange{background:rgba(255,167,38,.12);color:var(--accent-orange);border:1px solid rgba(255,167,38,.3)}

/* ═══ BADGES ═══ */
.sold-badge-row{display:inline-flex;align-items:center;gap:.2rem;font-size:.62rem;font-weight:800;letter-spacing:.4px;background:rgba(255,101,132,.18);color:var(--accent-red);border:1px solid rgba(255,101,132,.4);border-radius:4px;padding:.12rem .4rem;text-transform:uppercase;margin-top:.2rem}
.bought-badge-row{display:inline-flex;align-items:center;gap:.2rem;font-size:.62rem;font-weight:800;letter-spacing:.4px;background:rgba(67,217,173,.2);color:var(--secondary);border:1px solid rgba(67,217,173,.4);border-radius:4px;padding:.12rem .4rem;text-transform:uppercase;margin-top:.2rem}

/* ═══ ACTIONS ═══ */
.action-group{display:flex;align-items:center;justify-content:center;gap:.35rem}
.btn-icon{width:32px;height:32px;border-radius:var(--radius-sm);border:1px solid var(--border);background:#FFFFFF;color:var(--text-secondary);display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:var(--transition);text-decoration:none;flex-shrink:0;box-shadow:0 1px 2px rgba(15,23,42,0.04)}
.btn-icon:hover{background:#EEF2FF;border-color:var(--primary);color:var(--primary);transform:translateY(-1px);box-shadow:0 2px 4px rgba(91,82,224,0.15)}
.btn-icon.danger:hover{background:#FEF2F2;border-color:var(--accent-red);color:var(--accent-red);transform:translateY(-1px);box-shadow:0 2px 4px rgba(239,68,68,0.15)}
.btn-icon.success:hover{background:#ECFDF5;border-color:var(--secondary);color:var(--secondary);transform:translateY(-1px);box-shadow:0 2px 4px rgba(16,185,129,0.15)}
.btn-icon.disabled{opacity:.4;cursor:not-allowed;pointer-events:none;background:#F8FAFC;border-color:var(--border);color:var(--text-muted);box-shadow:none}
.btn-icon .material-icons-round{font-size:1.05rem}

/* ═══ AI CHIP ═══ */
.ai-chip-sm{display:inline-flex;align-items:center;gap:.2rem;padding:.15rem .45rem;background:linear-gradient(135deg,rgba(108,99,255,.15),rgba(67,217,173,.08));border:1px solid rgba(108,99,255,.3);border-radius:100px;font-size:.68rem;font-weight:700;color:var(--primary-light)}

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

@media(max-width:1100px){.stats-strip{grid-template-columns:repeat(2,1fr)}}
@media(max-width:650px){.stats-strip{grid-template-columns:1fr}.toolbar{flex-direction:column;align-items:stretch}}
</style>
@endsection

@section('content')
<div class="animate-fade-in-up">

    {{-- PAGE HEADER --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Mes Articles & Transactions</h1>
            <p class="page-subtitle">Gérez vos publications en vente et suivez tous vos articles achetés</p>
        </div>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap">
            <a href="{{ route('armoire.index') }}" class="btn btn-secondary">
                <span class="material-icons-round">checkroom</span> Mon Dressing
            </a>
            <a href="{{ route('marketplace.create') }}" class="btn btn-primary" id="btn-new-article">
                <span class="material-icons-round">add_circle</span> Nouvel article
            </a>
        </div>
    </div>

    {{-- STATS STRIP --}}
    <div class="stats-strip">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--gradient-primary)">
                <span class="material-icons-round">inventory_2</span>
            </div>
            <div>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Mes publications</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#43D9AD,#29B6F6)">
                <span class="material-icons-round">shopping_bag</span>
            </div>
            <div>
                <div class="stat-value" style="color:var(--secondary)">{{ $stats['mes_achats'] }}</div>
                <div class="stat-label">Articles achetés</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#FF6584,#FF8E53)">
                <span class="material-icons-round">sell</span>
            </div>
            <div>
                <div class="stat-value" style="color:var(--accent-red)">{{ $stats['vendu'] }}</div>
                <div class="stat-label">Mes ventes finalisées</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#FFA726,#FF7043)">
                <span class="material-icons-round">payments</span>
            </div>
            <div>
                <div class="stat-value" style="color:var(--accent-orange)">{{ number_format($stats['total_depense'], 2) }} DT</div>
                <div class="stat-label">Total dépensé en seconde main</div>
            </div>
        </div>
    </div>

    {{-- TABS NAVIGATION --}}
    <div class="tabs-nav">
        <a href="{{ route('marketplace.mes-articles', ['tab' => 'ventes']) }}"
           class="tab-btn {{ $activeTab === 'ventes' ? 'active' : '' }}"
           id="tab-btn-ventes">
            <span class="material-icons-round" style="font-size:1.15rem">storefront</span>
            <span>Mes Annonces publiées</span>
            <span class="badge-count">{{ $stats['total'] }}</span>
        </a>
        <a href="{{ route('marketplace.mes-articles', ['tab' => 'achats']) }}"
           class="tab-btn {{ $activeTab === 'achats' ? 'active' : '' }}"
           id="tab-btn-achats">
            <span class="material-icons-round" style="font-size:1.15rem">shopping_bag</span>
            <span>Mes Articles Achetés</span>
            <span class="badge-count">{{ $stats['mes_achats'] }}</span>
        </a>
    </div>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- ONGLET 1 : MES ANNONCES PUBLIÉES --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'ventes')

        {{-- TOOLBAR --}}
        <form method="GET" action="{{ route('marketplace.mes-articles') }}" id="filter-form">
            <input type="hidden" name="tab" value="ventes">
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
                        <option value="echange" {{ request('type') == 'echange' ? 'selected' : '' }}>Échange</option>
                    </select>
                    @if(request()->hasAny(['statut','type']))
                        <a href="{{ route('marketplace.mes-articles', ['tab' => 'ventes']) }}" class="btn btn-secondary btn-sm" id="btn-reset-filters">
                            <span class="material-icons-round" style="font-size:.95rem">close</span> Réinitialiser
                        </a>
                    @endif
                </div>
                <div style="color:var(--text-secondary);font-size:.85rem;white-space:nowrap">
                    <strong style="color:var(--text-primary)">{{ $articles->total() }}</strong> article{{ $articles->total() > 1 ? 's' : '' }}
                </div>
            </div>
        </form>

        {{-- TABLE DES ANNONCES --}}
        <div class="articles-table-wrap">
            @if($articles->isEmpty())
                <div class="empty-state">
                    <span class="material-icons-round">store</span>
                    <h3>Aucun article publié</h3>
                    <p>Vous n'avez pas encore publié d'article sur la marketplace.<br>Donnez une seconde vie à vos vêtements dès aujourd'hui !</p>
                    <a href="{{ route('marketplace.create') }}" class="btn btn-primary" id="btn-first-article">
                        <span class="material-icons-round">add_circle</span> Publier mon premier article
                    </a>
                </div>
            @else
                <div class="table-scroll">
                    <table class="articles-table" id="articles-table">
                        <thead>
                            <tr>
                                <th style="min-width:200px">Article</th>
                                <th>Type</th>
                                <th>Prix</th>
                                <th>État</th>
                                <th style="min-width:170px">Statut & Acheteur</th>
                                <th>Score IA</th>
                                <th>Vues</th>
                                <th>Date</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($articles as $article)
                            @php
                                $effectiveType = ($article->type === 'don') ? 'vente' : $article->type;
                                $displayPrice  = $article->prix ?? $article->ai_prix_min ?? 15.00;
                                $isVendu       = $article->statut === 'vendu';
                            @endphp
                            <tr id="row-{{ $article->id }}" class="{{ $isVendu ? 'row-vendu' : '' }}">

                                {{-- ARTICLE --}}
                                <td>
                                    <div class="product-cell">
                                        <div class="product-thumb">
                                            <img src="{{ $article->display_image_url }}" alt="{{ $article->titre }}" onerror="this.src='/images/default-clothing.jpg'">
                                        </div>
                                        <div style="overflow:hidden">
                                            <div class="product-name" title="{{ $article->titre }}">{{ $article->titre }}</div>
                                            <div class="product-sub">{{ $article->marque ?? 'Sans marque' }} &bull; T.{{ $article->taille }}</div>
                                            @if($isVendu)
                                                <span class="sold-badge-row">&#10003; Vendu</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- TYPE --}}
                                <td>
                                    <span class="type-pill type-{{ $effectiveType }}">
                                        {{ $effectiveType == 'vente' ? '💰' : '🔄' }}
                                        {{ ucfirst($effectiveType) }}
                                    </span>
                                </td>

                                {{-- PRIX --}}
                                <td>
                                    @if($effectiveType == 'echange')
                                        <span style="color:var(--accent-orange);font-weight:700;font-size:.83rem">Échange</span>
                                    @else
                                        <span style="font-family:'Outfit',sans-serif;font-weight:800;color:var(--primary-light);font-size:.9rem">
                                            {{ number_format($displayPrice, 2) }} DT
                                        </span>
                                    @endif
                                </td>

                                {{-- ÉTAT --}}
                                <td style="font-size:.8rem;color:var(--text-secondary);max-width:90px;overflow:hidden;text-overflow:ellipsis">
                                    {{ $article->etat }}
                                </td>

                                {{-- STATUT + ACHETEUR --}}
                                <td>
                                    <div class="status-cell">
                                        <span class="status-pill status-{{ $article->statut }}">
                                            @if($article->statut == 'disponible') ✅ Disponible
                                            @elseif($article->statut == 'en_cours') ⏳ En cours
                                            @else 💰 Vendu @endif
                                        </span>
                                        @if($isVendu && $article->acheteur)
                                            <div class="user-inline">
                                                <div class="user-avatar">
                                                    <span class="material-icons-round">person</span>
                                                </div>
                                                <div class="user-info">
                                                    <div class="user-name" title="{{ $article->acheteur->name }}">{{ $article->acheteur->name }}</div>
                                                    <div class="user-sub" title="{{ $article->acheteur->email }}">{{ $article->acheteur->email }}</div>
                                                </div>
                                            </div>
                                        @elseif($isVendu)
                                            <span style="font-size:.7rem;color:var(--text-muted)">Acheteur anonyme</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- SCORE IA --}}
                                <td>
                                    @if($article->ai_score)
                                        <span class="ai-chip-sm">✦ {{ $article->ai_score }}%</span>
                                    @else
                                        <span style="color:var(--text-muted);font-size:.78rem">-</span>
                                    @endif
                                </td>

                                {{-- VUES --}}
                                <td>
                                    <span style="display:flex;align-items:center;gap:.3rem;color:var(--text-muted);font-size:.82rem">
                                        <span class="material-icons-round" style="font-size:.9rem">visibility</span>
                                        {{ $article->vues ?? 0 }}
                                    </span>
                                </td>

                                {{-- DATE --}}
                                <td style="color:var(--text-muted);font-size:.8rem;white-space:nowrap">
                                    {{ $article->created_at->format('d/m/Y') }}
                                </td>

                                {{-- ACTIONS sticky --}}
                                <td class="col-actions">
                                    <div class="action-group">
                                        <a href="{{ route('marketplace.show', $article) }}"
                                           class="btn-icon"
                                           title="Voir l'annonce"
                                           id="btn-view-{{ $article->id }}">
                                            <span class="material-icons-round">visibility</span>
                                        </a>

                                        @if($isVendu)
                                            <span class="btn-icon disabled" title="Article vendu - modification impossible">
                                                <span class="material-icons-round">edit_off</span>
                                            </span>
                                        @else
                                            <a href="{{ route('marketplace.edit', $article) }}"
                                               class="btn-icon"
                                               title="Modifier"
                                               id="btn-edit-{{ $article->id }}">
                                                <span class="material-icons-round">edit</span>
                                            </a>
                                        @endif

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
                        {{ $articles->appends(['tab' => 'ventes'])->links() }}
                    </div>
                @endif
            @endif
        </div>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- ONGLET 2 : MES ARTICLES ACHETÉS --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    @else

        <div class="articles-table-wrap">
            @if($articlesAchetes->isEmpty())
                <div class="empty-state">
                    <span class="material-icons-round" style="color:var(--secondary)">shopping_cart_checkout</span>
                    <h3>Aucun vêtement acheté pour le moment</h3>
                    <p>Découvrez les milliers de pièces de seconde main certifiées par notre IA et trouvez votre prochaine pépite éco-responsable !</p>
                    <a href="{{ route('marketplace.index') }}" class="btn btn-primary" id="btn-explore-market">
                        <span class="material-icons-round">storefront</span> Explorer la Marketplace
                    </a>
                </div>
            @else
                <div class="table-scroll">
                    <table class="articles-table" id="achats-table">
                        <thead>
                            <tr>
                                <th style="min-width:200px">Vêtement Acheté</th>
                                <th>Type</th>
                                <th>Prix Payé</th>
                                <th>État</th>
                                <th style="min-width:170px">Vendeur</th>
                                <th>Catégorie</th>
                                <th>Date d'Achat</th>
                                <th class="col-actions">Actions & Dressing</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($articlesAchetes as $achat)
                            <tr id="achat-row-{{ $achat->id }}" class="row-achete">

                                {{-- ARTICLE --}}
                                <td>
                                    <div class="product-cell">
                                        <div class="product-thumb">
                                            <img src="{{ $achat->display_image_url }}" alt="{{ $achat->titre }}" onerror="this.src='/images/default-clothing.jpg'">
                                        </div>
                                        <div style="overflow:hidden">
                                            <div class="product-name" title="{{ $achat->titre }}">{{ $achat->titre }}</div>
                                            <div class="product-sub">{{ $achat->marque ?? 'Sans marque' }} &bull; T.{{ $achat->taille }}</div>
                                            <span class="bought-badge-row">✨ Acheté</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- TYPE --}}
                                <td>
                                    <span class="type-pill type-vente">
                                        💰 Achat
                                    </span>
                                </td>

                                {{-- PRIX --}}
                                <td>
                                    <span style="font-family:'Outfit',sans-serif;font-weight:800;color:var(--secondary);font-size:.92rem">
                                        {{ number_format($achat->prix ?? 15, 2) }} DT
                                    </span>
                                </td>

                                {{-- ÉTAT --}}
                                <td style="font-size:.8rem;color:var(--text-secondary)">
                                    {{ $achat->etat }}
                                </td>

                                {{-- VENDEUR --}}
                                <td>
                                    <div class="user-inline">
                                        <div class="user-avatar" style="background:var(--gradient-green)">
                                            <span class="material-icons-round">store</span>
                                        </div>
                                        <div class="user-info">
                                            <div class="user-name" title="{{ $achat->vendeur->name ?? 'Vendeur' }}">
                                                {{ $achat->vendeur->name ?? 'Vendeur TexTileCycle' }}
                                            </div>
                                            <div class="user-sub">
                                                ★ {{ $achat->note_vendeur ?? 4.5 }}/5
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- CATÉGORIE --}}
                                <td style="font-size:.8rem;color:var(--text-muted)">
                                    {{ $achat->categorie ?? 'Prêt-à-porter' }}
                                </td>

                                {{-- DATE --}}
                                <td style="color:var(--text-muted);font-size:.8rem;white-space:nowrap">
                                    {{ $achat->updated_at->format('d/m/Y H:i') }}
                                </td>

                                {{-- ACTIONS --}}
                                <td class="col-actions">
                                    <div class="action-group">
                                        <a href="{{ route('marketplace.show', $achat) }}"
                                           class="btn-icon"
                                           title="Voir la fiche détaillée">
                                            <span class="material-icons-round">visibility</span>
                                        </a>

                                        <a href="{{ route('armoire.index') }}"
                                           class="btn-icon success"
                                           title="Voir dans mon Armoire / Dressing">
                                            <span class="material-icons-round">checkroom</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($articlesAchetes->hasPages())
                    <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;justify-content:center">
                        {{ $articlesAchetes->appends(['tab' => 'achats'])->links() }}
                    </div>
                @endif
            @endif
        </div>

    @endif

    {{-- QUICK LINKS FOOTER --}}
    <div style="display:flex;gap:.75rem;margin-top:1.5rem;flex-wrap:wrap">
        <a href="{{ route('marketplace.index') }}" class="btn btn-secondary btn-sm">
            <span class="material-icons-round" style="font-size:1rem">storefront</span> Découvrir la marketplace
        </a>
        <a href="{{ route('marketplace.favoris') }}" class="btn btn-secondary btn-sm">
            <span class="material-icons-round" style="font-size:1rem">favorite</span> Mes Favoris
        </a>
        <a href="{{ route('armoire.index') }}" class="btn btn-secondary btn-sm">
            <span class="material-icons-round" style="font-size:1rem">checkroom</span> Mon Dressing Personnel
        </a>
    </div>

</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div class="modal-overlay" id="delete-modal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <span class="material-icons-round warn-icon">warning_amber</span>
        <h3>Supprimer cet article ?</h3>
        <p id="delete-modal-text">Cette action est irréversible. L'annonce sera définitivement supprimée.</p>
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
        'Vous êtes sur le point de supprimer "' + titre + '". Cette action est irréversible.';
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
