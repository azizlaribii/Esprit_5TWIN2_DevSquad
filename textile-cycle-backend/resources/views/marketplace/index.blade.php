@extends('layouts.app')

@section('title', 'Marketplace Circulaire')
@section('meta_description', 'Achetez, échangez ou donnez des vêtements récupérés et réparés sur TexTileCycle Marketplace')
@section('breadcrumb', 'Marketplace')

@section('styles')
<style>
.market-grid{display:grid;grid-template-columns:280px 1fr;gap:1.5rem;align-items:start}
.filter-panel{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem;position:sticky;top:90px}
.filter-title{font-size:.875rem;font-weight:700;color:var(--text-primary);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.filter-group{margin-bottom:1.25rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border)}
.filter-group:last-child{border-bottom:none;margin-bottom:0;padding-bottom:0}
.filter-label{font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.6rem}
.filter-option{display:flex;align-items:center;gap:.6rem;padding:.35rem 0;cursor:pointer;font-size:.85rem;color:var(--text-secondary)}
.filter-option:hover{color:var(--text-primary)} .filter-option input{accent-color:var(--primary)}
.range-input{width:100%;accent-color:var(--primary)}
.products-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem}
.products-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1.25rem}
.product-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;transition:var(--transition);cursor:pointer}
.product-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-glow);border-color:var(--border-active)}
.product-img{width:100%;height:180px;object-fit:cover;background:linear-gradient(135deg,rgba(108,99,255,.1),rgba(67,217,173,.05));display:flex;align-items:center;justify-content:center;font-size:3rem}
.product-body{padding:1rem}
.product-title{font-size:.9rem;font-weight:700;color:var(--text-primary);margin-bottom:.35rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.product-meta{font-size:.78rem;color:var(--text-muted);margin-bottom:.6rem}
.product-footer{display:flex;align-items:center;justify-content:space-between;margin-top:.75rem}
.product-price{font-family:'Outfit',sans-serif;font-size:1.25rem;font-weight:800;color:var(--primary-light)}
.product-price.free{color:var(--secondary)} .product-price.exchange{color:var(--accent-orange)}
.fav-btn{background:none;border:none;cursor:pointer;color:var(--text-muted);transition:var(--transition);padding:.25rem;border-radius:50%}
.fav-btn:hover,.fav-btn.active{color:var(--accent-red)} .fav-btn .material-icons-round{font-size:1.25rem}
.ai-section{background:linear-gradient(135deg,rgba(108,99,255,.08),rgba(255,101,132,.05));border:1px solid rgba(108,99,255,.2);border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:1.5rem}
.ai-section-header{display:flex;align-items:center;gap:.75rem;margin-bottom:1rem}
.search-bar{display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap}
.search-input-wrap{flex:1;position:relative;min-width:200px}
.search-input-wrap .material-icons-round{position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:1.1rem}
.search-input-wrap input{padding-left:2.5rem}
.condition-badge{font-size:.7rem;padding:.15rem .5rem;border-radius:100px;font-weight:700}
.condition-neuf{background:rgba(67,217,173,.15);color:var(--secondary);border:1px solid rgba(67,217,173,.3)}
.condition-bon{background:rgba(41,182,246,.15);color:var(--accent-blue);border:1px solid rgba(41,182,246,.3)}
.condition-moyen{background:rgba(255,167,38,.15);color:var(--accent-orange);border:1px solid rgba(255,167,38,.3)}
.condition-use{background:rgba(255,101,132,.15);color:var(--accent-red);border:1px solid rgba(255,101,132,.3)}
.type-badge-vente{background:rgba(108,99,255,.15);color:var(--primary-light);border:1px solid rgba(108,99,255,.3)}
.type-badge-echange{background:rgba(255,167,38,.15);color:var(--accent-orange);border:1px solid rgba(255,167,38,.3)}
.type-badge-don{background:rgba(67,217,173,.15);color:var(--secondary);border:1px solid rgba(67,217,173,.3)}
.ai-score{display:flex;align-items:center;gap:.35rem;font-size:.75rem;color:var(--primary-light);font-weight:600}
.seller-info{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:var(--text-muted);margin-top:.35rem}
.stars{color:#FFD700;font-size:.75rem}
@media(max-width:1100px){.market-grid{grid-template-columns:1fr}.filter-panel{position:static}}
</style>
@endsection

@section('content')
<div class="animate-fade-in-up">
    {{-- Page Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Marketplace Circulaire</h1>
            <p class="page-subtitle">
                Vente, échange et don de vêtements récupérés & réparés
                <span class="ai-badge" style="margin-left:.5rem">IA Recommandations</span>
            </p>
        </div>
        <div style="display:flex;gap:.75rem;align-items:center">
            <a href="{{ route('marketplace.favoris') }}" class="btn btn-secondary">
                <span class="material-icons-round">favorite</span> Mes Favoris
            </a>
            <a href="{{ route('marketplace.create') }}" class="btn btn-primary" id="btn-publish">
                <span class="material-icons-round">add_circle</span> Publier un article
            </a>
        </div>
    </div>

    {{-- ═══ AI RECOMMENDATIONS ═══ --}}
    @include('partials.marketplace.ai-recommendations', ['recommendations' => $recommendations ?? []])

    {{-- ═══ SEARCH BAR ═══ --}}
    <div class="search-bar section">
        <div class="search-input-wrap">
            <span class="material-icons-round">search</span>
            <form method="GET" action="{{ route('marketplace.index') }}" id="search-form">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" id="search-input" placeholder="Rechercher jean, veste, robe…" oninput="this.form.submit()">
        </div>
        <select name="categorie" class="form-control" style="width:160px" onchange="this.form.submit()" id="filter-categorie">
            <option value="">Toutes catégories</option>
            @foreach(['T-Shirts', 'Jeans', 'Vestes', 'Robes', 'Manteaux', 'Chaussures', 'Accessoires'] as $cat)
                <option value="{{ $cat }}" {{ request('categorie') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <select name="type" class="form-control" style="width:140px" onchange="this.form.submit()" id="filter-type">
            <option value="">Tout type</option>
            <option value="vente" {{ request('type') == 'vente' ? 'selected' : '' }}>Vente 💰</option>
            <option value="echange" {{ request('type') == 'echange' ? 'selected' : '' }}>Échange 🔄</option>
            <option value="don" {{ request('type') == 'don' ? 'selected' : '' }}>Don ❤️</option>
        </select>
        <select name="taille" class="form-control" style="width:120px" onchange="this.form.submit()" id="filter-taille">
            <option value="">Taille</option>
            @foreach(['XS','S','M','L','XL','XXL','36','38','40','42','44','46'] as $t)
                <option value="{{ $t }}" {{ request('taille') == $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
        </form>
    </div>

    {{-- ═══ MAIN LAYOUT ═══ --}}
    <div class="market-grid">

        {{-- ─── FILTER PANEL ─── --}}
        <aside class="filter-panel" id="filter-panel">
            <div class="filter-title">
                <span class="material-icons-round" style="font-size:1.1rem;color:var(--primary)">tune</span>
                Filtres avancés
            </div>
            <form method="GET" action="{{ route('marketplace.index') }}" id="filter-form">
                <input type="hidden" name="q" value="{{ request('q') }}">

                <div class="filter-group">
                    <div class="filter-label">Prix (€)</div>
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;font-size:.8rem;color:var(--text-secondary)">
                        <span id="price-min-label">{{ request('prix_min', 0) }}€</span>
                        <span>—</span>
                        <span id="price-max-label">{{ request('prix_max', 500) }}€</span>
                    </div>
                    <input type="range" name="prix_min" class="range-input" min="0" max="500" value="{{ request('prix_min', 0) }}" id="range-min" oninput="document.getElementById('price-min-label').textContent=this.value+'€'">
                    <input type="range" name="prix_max" class="range-input" min="0" max="500" value="{{ request('prix_max', 500) }}" id="range-max" oninput="document.getElementById('price-max-label').textContent=this.value+'€'">
                </div>

                <div class="filter-group">
                    <div class="filter-label">État</div>
                    @foreach(['Neuf avec étiquette' => 'neuf', 'Très bon état' => 'tres_bon', 'Bon état' => 'bon', 'État correct' => 'correct'] as $label => $val)
                        <label class="filter-option">
                            <input type="checkbox" name="etat[]" value="{{ $val }}" {{ in_array($val, (array)request('etat')) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>

                <div class="filter-group">
                    <div class="filter-label">Genre</div>
                    @foreach(['Homme','Femme','Enfant','Unisexe'] as $g)
                        <label class="filter-option">
                            <input type="checkbox" name="genre[]" value="{{ $g }}" {{ in_array($g, (array)request('genre')) ? 'checked' : '' }}>
                            {{ $g }}
                        </label>
                    @endforeach
                </div>

                <div class="filter-group">
                    <div class="filter-label">Tri</div>
                    <select name="tri" class="form-control form-control-sm" id="filter-sort">
                        <option value="recent" {{ request('tri') == 'recent' ? 'selected' : '' }}>Plus récents</option>
                        <option value="prix_asc" {{ request('tri') == 'prix_asc' ? 'selected' : '' }}>Prix croissant</option>
                        <option value="prix_desc" {{ request('tri') == 'prix_desc' ? 'selected' : '' }}>Prix décroissant</option>
                        <option value="ai_score" {{ request('tri') == 'ai_score' ? 'selected' : '' }}>Score IA ✦</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%" id="btn-apply-filters">
                    <span class="material-icons-round" style="font-size:1rem">filter_list</span>
                    Appliquer les filtres
                </button>
                <a href="{{ route('marketplace.index') }}" class="btn btn-secondary" style="width:100%;margin-top:.5rem;justify-content:center">
                    Réinitialiser
                </a>
            </form>
        </aside>

        {{-- ─── PRODUCTS GRID ─── --}}
        <main>
            <div class="products-header">
                <div style="color:var(--text-secondary);font-size:.875rem">
                    <strong style="color:var(--text-primary)">{{ $articles->total() }}</strong> article{{ $articles->total() > 1 ? 's' : '' }} trouvé{{ $articles->total() > 1 ? 's' : '' }}
                </div>
            </div>

            @if($articles->isEmpty())
                <div class="card" style="text-align:center;padding:3rem">
                    <span class="material-icons-round" style="font-size:3rem;color:var(--text-muted)">search_off</span>
                    <p style="color:var(--text-muted);margin-top:.75rem">Aucun article trouvé pour cette recherche.</p>
                    <a href="{{ route('marketplace.create') }}" class="btn btn-primary" style="margin-top:1rem;display:inline-flex">
                        <span class="material-icons-round">add</span> Publier le premier !
                    </a>
                </div>
            @else
                <div class="products-grid" id="products-grid">
                    @foreach($articles as $article)
                        <div class="product-card" id="product-{{ $article->id }}">
                            {{-- Image --}}
                            <a href="{{ route('marketplace.show', $article) }}">
                                <div class="product-img">
                                    @if($article->image_url)
                                        <img src="{{ asset('storage/'.$article->image_url) }}" alt="{{ $article->titre }}" style="width:100%;height:100%;object-fit:cover">
                                    @else
                                        <span style="font-size:3rem">{{ ['👕','👗','👖','🧥','👟','🎽','🧢'][array_rand(['👕','👗','👖','🧥','👟','🎽','🧢'])] }}</span>
                                    @endif
                                </div>
                            </a>

                            <div class="product-body">
                                {{-- Badges --}}
                                <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.5rem;flex-wrap:wrap">
                                    <span class="badge {{ $article->type == 'vente' ? 'type-badge-vente' : ($article->type == 'echange' ? 'type-badge-echange' : 'type-badge-don') }} condition-badge">
                                        {{ $article->type == 'vente' ? '💰' : ($article->type == 'echange' ? '🔄' : '❤️') }} {{ ucfirst($article->type) }}
                                    </span>
                                    <span class="badge condition-badge {{ $article->etat == 'neuf' || $article->etat == 'Neuf avec étiquette' ? 'condition-neuf' : ($article->etat == 'tres_bon' || str_contains($article->etat,'Très') ? 'condition-bon' : ($article->etat == 'bon' || str_contains($article->etat,'Bon') ? 'condition-bon' : 'condition-moyen')) }}">
                                        {{ $article->etat }}
                                    </span>
                                    @if($article->ai_score > 85)
                                        <span class="ai-score">✦ {{ $article->ai_score }}%</span>
                                    @endif
                                </div>

                                <a href="{{ route('marketplace.show', $article) }}">
                                    <div class="product-title">{{ $article->titre }}</div>
                                </a>
                                <div class="product-meta">{{ $article->categorie }} • Taille {{ $article->taille }} • {{ $article->genre }}</div>

                                {{-- Seller --}}
                                <div class="seller-info">
                                    <span class="material-icons-round" style="font-size:.9rem">person</span>
                                    {{ $article->vendeur->name ?? 'Anonyme' }}
                                    <span class="stars">{{ str_repeat('★', intval($article->note_vendeur ?? 4)) }}</span>
                                </div>

                                <div class="product-footer">
                                    <div class="product-price {{ $article->type == 'don' ? 'free' : ($article->type == 'echange' ? 'exchange' : '') }}">
                                        @if($article->type == 'don') Gratuit
                                        @elseif($article->type == 'echange') Échange
                                        @else {{ number_format($article->prix, 2) }} €
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('marketplace.toggle-favori', $article) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="fav-btn {{ $article->is_favori ? 'active' : '' }}" id="fav-{{ $article->id }}">
                                            <span class="material-icons-round">{{ $article->is_favori ? 'favorite' : 'favorite_border' }}</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div style="margin-top:1.5rem;display:flex;justify-content:center">
                    {{ $articles->withQueryString()->links() }}
                </div>
            @endif
        </main>
    </div>
</div>
@endsection
