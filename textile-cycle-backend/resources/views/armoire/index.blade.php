@extends('layouts.app')

@section('title', 'Mon Armoire – Dossiers & Sections du Dressing')
@section('meta_description', 'Organisez vos vêtements par dossiers : T-shirts, Pantalons, Chaussures, Chaussettes, et publiez-les sur la Marketplace.')
@section('breadcrumb', 'Dressing › Mon Armoire')

@section('styles')
<style>
/* ═══ ARMOIRE STYLES ═══ */
.armoire-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.armoire-hero {
    background: linear-gradient(135deg, rgba(108,99,255,.14) 0%, rgba(255,101,132,.09) 100%);
    border: 1px solid rgba(108,99,255,.25);
    border-radius: var(--radius-lg);
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.25rem;
}
.armoire-hero-text h2 {
    font-size: 1.45rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: .35rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.armoire-hero-text p {
    color: var(--text-secondary);
    font-size: .88rem;
    max-width: 680px;
    margin: 0;
}

/* ═══ DOSSIERS / SECTIONS STRIP ═══ */
.dossiers-shelf {
    display: flex;
    align-items: center;
    gap: .75rem;
    overflow-x: auto;
    padding-bottom: .5rem;
    margin-bottom: 1.75rem;
}
.dossier-pill {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    padding: .65rem 1.15rem;
    border-radius: var(--radius-md);
    background: var(--bg-card);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    font-weight: 700;
    font-size: .88rem;
    text-decoration: none;
    transition: var(--transition);
    white-space: nowrap;
    cursor: pointer;
}
.dossier-pill:hover {
    border-color: var(--primary);
    color: var(--text-primary);
    transform: translateY(-2px);
}
.dossier-pill.active {
    background: linear-gradient(135deg, rgba(108,99,255,.25), rgba(67,217,173,.15));
    border-color: var(--primary);
    color: var(--primary-light);
    box-shadow: 0 4px 15px rgba(108,99,255,.2);
}
.dossier-count {
    padding: .15rem .5rem;
    border-radius: 100px;
    font-size: .72rem;
    font-weight: 800;
    background: rgba(255,255,255,.08);
    color: var(--text-muted);
}
.dossier-pill.active .dossier-count {
    background: var(--primary);
    color: white;
}

/* ═══ GRID LAYOUT ═══ */
.armoire-layout {
    display: grid;
    grid-template-columns: 370px 1fr;
    gap: 1.75rem;
    align-items: start;
}
@media (max-width: 1050px) {
    .armoire-layout {
        grid-template-columns: 1fr;
    }
}

/* ═══ UPLOAD CARD ═══ */
.upload-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    position: sticky;
    top: 90px;
}
.drop-zone {
    border: 2px dashed rgba(108,99,255,.35);
    border-radius: var(--radius-md);
    background: rgba(108,99,255,.03);
    padding: 1.5rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
    margin-bottom: 1.25rem;
    position: relative;
    overflow: hidden;
}
.drop-zone:hover {
    border-color: var(--primary);
    background: rgba(108,99,255,.08);
}
.drop-zone-icon {
    font-size: 2.5rem;
    color: var(--primary-light);
    margin-bottom: .35rem;
}
.drop-zone-text {
    font-weight: 700;
    color: var(--text-primary);
    font-size: .88rem;
    margin-bottom: .2rem;
}
.drop-zone-hint {
    font-size: .74rem;
    color: var(--text-muted);
}
.preview-img-container {
    width: 100%;
    height: 180px;
    border-radius: var(--radius-md);
    overflow: hidden;
    margin-bottom: 1rem;
    border: 1px solid var(--border);
    display: none;
    position: relative;
}
.preview-img-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ═══ FOLDER SELECTION BUTTONS ═══ */
.folder-btn-group {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: .5rem;
    margin-bottom: .75rem;
}
.folder-option-btn {
    display: flex;
    align-items: center;
    gap: .4rem;
    padding: .6rem .75rem;
    border-radius: var(--radius-sm);
    background: rgba(255,255,255,.03);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    font-size: .82rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    text-align: left;
}
.folder-option-btn:hover {
    border-color: var(--primary-light);
    color: var(--text-primary);
}
.folder-option-btn.selected {
    background: rgba(108,99,255,.18);
    border-color: var(--primary);
    color: var(--primary-light);
}

/* ═══ ITEMS GRID ═══ */
.armoire-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1.25rem;
}
.armoire-item-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
}
.armoire-item-card:hover {
    transform: translateY(-3px);
    border-color: var(--border-active);
    box-shadow: var(--shadow-glow);
}
.armoire-item-img {
    height: 220px;
    width: 100%;
    background: rgba(255,255,255,.02);
    position: relative;
    overflow: hidden;
}
.armoire-item-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .3s ease;
}
.armoire-item-card:hover .armoire-item-img img {
    transform: scale(1.04);
}
.dossier-tag-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 100px;
    padding: .25rem .65rem;
    font-size: .75rem;
    font-weight: 700;
    color: white;
    display: flex;
    align-items: center;
    gap: .3rem;
}
.armoire-item-body {
    padding: 1.15rem;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.armoire-item-title {
    font-weight: 700;
    font-size: .95rem;
    color: var(--text-primary);
    margin-bottom: .35rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.armoire-item-meta {
    font-size: .78rem;
    color: var(--text-muted);
    margin-bottom: .75rem;
}
.armoire-item-actions {
    margin-top: auto;
    display: flex;
    align-items: center;
    gap: .5rem;
    padding-top: .75rem;
    border-top: 1px solid rgba(255,255,255,.05);
}
.folder-switch-select {
    background: rgba(255,255,255,.04);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    border-radius: var(--radius-xs);
    font-size: .72rem;
    padding: .2rem .4rem;
}
</style>
@endsection

@section('content')
<div class="animate-fade-in-up">

    {{-- HEADER --}}
    <div class="armoire-header">
        <div>
            <h1 class="page-title" style="display:flex;align-items:center;gap:.6rem">
                <span class="material-icons-round" style="color:var(--primary);font-size:2rem">checkroom</span>
                Mon Armoire & Dressing
            </h1>
            <p class="page-subtitle">Organisez vos vêtements par dossiers (T-shirts, Pantalons, Chaussures, Chaussettes). Préparez vos articles et publiez-les en 1 clic !</p>
        </div>
        <div style="display:flex;gap:.75rem">
            <a href="{{ route('marketplace.index') }}" class="btn btn-secondary">
                <span class="material-icons-round">storefront</span> Marketplace
            </a>
            <a href="{{ route('marketplace.create') }}" class="btn btn-primary">
                <span class="material-icons-round">add_circle</span> Créer une annonce
            </a>
        </div>
    </div>

    {{-- HERO BANNER --}}
    <div class="armoire-hero">
        <div class="armoire-hero-text">
            <h2>
                <span>📁</span> Rayons & Dossiers personnalisés
            </h2>
            <p>
                Classez facilement vos pièces par type : <strong>👕 T-shirts</strong>, <strong>👖 Pantalons</strong>, <strong>👟 Chaussures</strong> ou <strong>🧦 Chaussettes</strong>.
                Vous pouvez également créer de nouveaux dossiers personnalisés pour ranger votre dressing comme vous le souhaitez.
            </p>
        </div>
        <div style="display:flex;gap:1rem">
            <div style="background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:var(--radius-md);padding:.75rem 1.25rem;text-align:center">
                <div style="font-family:'Outfit',sans-serif;font-size:1.5rem;font-weight:800;color:var(--primary-light)">{{ $stats['total'] }}</div>
                <div style="font-size:.72rem;color:var(--text-muted);font-weight:600;text-transform:uppercase">Vêtements</div>
            </div>
            <div style="background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:var(--radius-md);padding:.75rem 1.25rem;text-align:center">
                <div style="font-family:'Outfit',sans-serif;font-size:1.5rem;font-weight:800;color:var(--secondary)">{{ $stats['dossiers'] }}</div>
                <div style="font-size:.72rem;color:var(--text-muted);font-weight:600;text-transform:uppercase">Dossiers</div>
            </div>
        </div>
    </div>

    {{-- ═══ DOSSIERS / SECTIONS SHELF NAVIGATION ═══ --}}
    @php
        $currentDossier = request('dossier', 'all');
        $folderIcons = [
            'T-shirt'    => '👕',
            'Pantalon'   => '👖',
            'Chaussures' => '👟',
            'Chaussettes'=> '🧦',
        ];
    @endphp
    <div class="dossiers-shelf">
        {{-- All / Tout le dressing --}}
        <a href="{{ route('armoire.index') }}" class="dossier-pill {{ $currentDossier === 'all' || !request()->filled('dossier') ? 'active' : '' }}">
            <span>📁</span> Tout le dressing
            <span class="dossier-count">{{ $stats['total'] }}</span>
        </a>

        {{-- Core 4 requested types: T-shirt, Pantalon, Chaussures, Chaussettes --}}
        @foreach(['T-shirt' => '👕 T-shirts', 'Pantalon' => '👖 Pantalons', 'Chaussures' => '👟 Chaussures', 'Chaussettes' => '🧦 Chaussettes'] as $dKey => $dLabel)
            @php $count = $countsByDossier[$dKey] ?? 0; @endphp
            <a href="{{ route('armoire.index', ['dossier' => $dKey]) }}" class="dossier-pill {{ $currentDossier === $dKey ? 'active' : '' }}">
                {{ $dLabel }}
                <span class="dossier-count">{{ $count }}</span>
            </a>
        @endforeach

        {{-- Custom folders --}}
        @foreach($dossiersList as $dName)
            @if(!in_array($dName, ['T-shirt', 'Pantalon', 'Chaussures', 'Chaussettes']))
                @php $count = $countsByDossier[$dName] ?? 0; @endphp
                <a href="{{ route('armoire.index', ['dossier' => $dName]) }}" class="dossier-pill {{ $currentDossier === $dName ? 'active' : '' }}">
                    <span>📁</span> {{ $dName }}
                    <span class="dossier-count">{{ $count }}</span>
                </a>
            @endif
        @endforeach
    </div>

    {{-- MAIN LAYOUT --}}
    <div class="armoire-layout">

        {{-- ─── LEFT: DEPOSIT CLOTHES WITH FOLDER ASSIGNMENT ─── --}}
        <div>
            <div class="upload-card">
                <div class="card-header" style="margin-bottom:1.25rem">
                    <div class="card-title">
                        <span class="material-icons-round" style="color:var(--primary);font-size:1.2rem">add_photo_alternate</span>
                        Déposer dans mon Armoire
                    </div>
                </div>

                <form method="POST" action="{{ route('armoire.store') }}" enctype="multipart/form-data" id="armoire-upload-form">
                    @csrf

                    {{-- Image Preview & Dropzone --}}
                    <div class="preview-img-container" id="armoire-preview-wrap">
                        <img id="armoire-preview-img" src="" alt="Aperçu vêtement">
                    </div>

                    <div class="drop-zone" id="armoire-dropzone" onclick="document.getElementById('armoire-file-input').click()">
                        <span class="material-icons-round drop-zone-icon">cloud_upload</span>
                        <div class="drop-zone-text">Déposez la photo du vêtement</div>
                        <div class="drop-zone-hint">JPG, PNG, WebP — max 5 Mo</div>
                    </div>
                    <input type="file" name="image" id="armoire-file-input" accept="image/*" style="display:none" required onchange="previewArmoireImage(this)">

                    {{-- AI SCAN BUTTON & STATUS --}}
                    <div style="margin-bottom:1rem">
                        <button type="button" class="btn btn-secondary btn-sm" id="btn-scan-ia-armoire"
                                style="width:100%;justify-content:center;gap:.45rem;padding:.55rem;font-size:.82rem"
                                onclick="scanArmoireWithIA()">
                            <span class="material-icons-round" style="color:var(--primary);font-size:1.1rem">auto_awesome</span>
                            Scanner la pièce avec l'IA
                        </button>
                    </div>
                    <div id="armoire-ai-status" style="display:none;margin-bottom:1rem;padding:.65rem .85rem;border-radius:var(--radius-sm);background:rgba(108,99,255,.08);border:1px solid rgba(108,99,255,.25);font-size:.78rem;color:var(--primary);line-height:1.4">
                    </div>
                    @error('image')
                        <div class="form-error" style="margin-bottom:1rem">
                            <span class="material-icons-round" style="font-size:.9rem">error</span> {{ $message }}
                        </div>
                    @enderror

                    {{-- SECTION / DOSSIER SELECTOR --}}
                    <div class="form-group">
                        <label class="form-label" style="display:flex;align-items:center;justify-content:space-between">
                            <span>Dossier / Section du dressing *</span>
                            <span style="font-size:.72rem;color:var(--primary-light);font-weight:600">Choisissez un type</span>
                        </label>
                        <input type="hidden" name="dossier" id="selected-dossier-input" value="{{ in_array($currentDossier, ['T-shirt','Pantalon','Chaussures','Chaussettes']) ? $currentDossier : 'T-shirt' }}">

                        <div class="folder-btn-group">
                            <button type="button" class="folder-option-btn {{ ($currentDossier === 'T-shirt' || !in_array($currentDossier, ['Pantalon','Chaussures','Chaussettes'])) ? 'selected' : '' }}" onclick="chooseDossier('T-shirt', 'T-Shirts & Tops', this)">
                                <span style="font-size:1.15rem">👕</span> T-shirt
                            </button>
                            <button type="button" class="folder-option-btn {{ $currentDossier === 'Pantalon' ? 'selected' : '' }}" onclick="chooseDossier('Pantalon', 'Jeans & Pantalons', this)">
                                <span style="font-size:1.15rem">👖</span> Pantalon
                            </button>
                            <button type="button" class="folder-option-btn {{ $currentDossier === 'Chaussures' ? 'selected' : '' }}" onclick="chooseDossier('Chaussures', 'Chaussures', this)">
                                <span style="font-size:1.15rem">👟</span> Chaussures
                            </button>
                            <button type="button" class="folder-option-btn {{ $currentDossier === 'Chaussettes' ? 'selected' : '' }}" onclick="chooseDossier('Chaussettes', 'Accessoires', this)">
                                <span style="font-size:1.15rem">🧦</span> Chaussettes
                            </button>
                        </div>

                        {{-- Ou créer un nouveau dossier personnalisé --}}
                        <div style="margin-top:.5rem">
                            <input type="text" name="nouveau_dossier" id="nouveau-dossier-input" class="form-control form-control-sm"
                                   placeholder="+ Ou nommer un autre dossier personnalisé..." oninput="handleCustomDossier(this)">
                        </div>
                    </div>

                    {{-- Titre --}}
                    <div class="form-group">
                        <label class="form-label" for="armoire-titre">Nom / Titre du vêtement</label>
                        <input type="text" name="titre" id="armoire-titre" class="form-control"
                               placeholder="Ex: T-shirt blanc col rond, Jean brut..." value="{{ old('titre') }}">
                    </div>

                    {{-- Catégorie, Marque & Taille --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                        <div class="form-group">
                            <label class="form-label" for="armoire-categorie">Catégorie</label>
                            <select name="categorie" id="armoire-categorie" class="form-control">
                                @foreach(['T-Shirts & Tops','Jeans & Pantalons','Chaussures','Accessoires','Vestes & Manteaux','Robes & Jupes','Sportswear','Lingerie'] as $cat)
                                    <option value="{{ $cat }}" {{ old('categorie') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="armoire-marque">Marque</label>
                            <input type="text" name="marque" id="armoire-marque" class="form-control"
                                   placeholder="Ex: Zara, Nike, Levi's..." value="{{ old('marque') }}">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                        <div class="form-group">
                            <label class="form-label" for="armoire-taille">Taille</label>
                            <select name="taille" id="armoire-taille" class="form-control">
                                <option value="">Taille...</option>
                                @foreach(['XS','S','M','L','XL','XXL','36','38','40','42','44','46'] as $t)
                                    <option value="{{ $t }}" {{ old('taille') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Genre & État --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                        <div class="form-group">
                            <label class="form-label" for="armoire-genre">Genre</label>
                            <select name="genre" id="armoire-genre" class="form-control">
                                <option value="Unisexe">Unisexe</option>
                                <option value="Femme">Femme</option>
                                <option value="Homme">Homme</option>
                                <option value="Enfant">Enfant</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="armoire-etat">État</label>
                            <select name="etat" id="armoire-etat" class="form-control">
                                <option value="Très bon état">Très bon état</option>
                                <option value="Neuf avec étiquette">Neuf avec étiquette</option>
                                <option value="Bon état">Bon état</option>
                                <option value="État correct">État correct</option>
                            </select>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="form-group">
                        <label class="form-label" for="armoire-desc">Description (optionnelle)</label>
                        <textarea name="description" id="armoire-desc" class="form-control" rows="2"
                                  placeholder="Matière, couleur, spécificités...">{{ old('description') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:.85rem">
                        <span class="material-icons-round">folder_special</span> Ranger dans mon Armoire
                    </button>
                </form>
            </div>
        </div>

        {{-- ─── RIGHT: CLOTHES GALLERY BY SECTION ─── --}}
        <div>
            {{-- Toolbar & Filters --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">
                <div style="display:flex;align-items:center;gap:.6rem">
                    <span style="font-size:1.2rem">
                        {{ $currentDossier === 'all' ? '📁' : ($folderIcons[$currentDossier] ?? '📁') }}
                    </span>
                    <strong style="font-size:1.05rem;color:var(--text-primary)">
                        {{ $currentDossier === 'all' ? 'Toutes les sections' : "Dossier : {$currentDossier}" }}
                    </strong>
                    <span class="badge badge-primary" style="font-size:.75rem">{{ $items->total() }} pièce{{ $items->total() > 1 ? 's' : '' }}</span>
                </div>

                <form method="GET" action="{{ route('armoire.index') }}" style="display:flex;gap:.5rem;flex:1;max-width:320px">
                    @if(request('dossier'))
                        <input type="hidden" name="dossier" value="{{ request('dossier') }}">
                    @endif
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Chercher dans ce dossier...">
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <span class="material-icons-round" style="font-size:1rem">search</span>
                    </button>
                </form>
            </div>

            @if($items->isEmpty())
                <div class="card" style="text-align:center;padding:4rem 2rem">
                    <span class="material-icons-round" style="font-size:4rem;color:var(--text-muted);display:block;margin-bottom:1rem">folder_open</span>
                    <h3 style="font-size:1.2rem;color:var(--text-primary);margin-bottom:.5rem">
                        {{ $currentDossier !== 'all' ? "Aucun vêtement dans le dossier '{$currentDossier}'" : "Votre armoire est vide" }}
                    </h3>
                    <p style="color:var(--text-muted);font-size:.9rem;max-width:420px;margin:0 auto 1.5rem">
                        Utilisez le formulaire à gauche pour déposer vos photos de {{ $currentDossier !== 'all' ? strtolower($currentDossier) : 'vêtements' }} et enrichir votre dressing !
                    </p>
                </div>
            @else
                <div class="armoire-grid">
                    @foreach($items as $item)
                        <div class="armoire-item-card">
                            <div class="armoire-item-img">
                                <img src="{{ $item->display_image_url }}" alt="{{ $item->titre }}" onerror="this.src='/images/default-clothing.jpg'">
                                {{-- Folder tag badge --}}
                                <div class="dossier-tag-badge">
                                    <span>{{ $item->dossier_icon }}</span>
                                    <span>{{ $item->dossier ?? 'T-shirt' }}</span>
                                </div>
                            </div>

                            <div class="armoire-item-body">
                                <div style="display:flex;align-items:center;gap:.35rem;margin-bottom:.5rem;flex-wrap:wrap">
                                    @if($item->categorie)
                                        <span class="badge badge-primary" style="font-size:.68rem">{{ $item->categorie }}</span>
                                    @endif
                                    @if($item->taille)
                                        <span class="badge badge-info" style="font-size:.68rem">T.{{ $item->taille }}</span>
                                    @endif
                                    @if($item->etat)
                                        <span class="badge badge-success" style="font-size:.68rem">{{ $item->etat }}</span>
                                    @endif
                                </div>

                                <div class="armoire-item-title" title="{{ $item->titre }}">{{ $item->titre }}</div>
                                <div class="armoire-item-meta">
                                    {{ $item->genre ?? 'Unisexe' }} &bull; Rangé le {{ $item->created_at->format('d/m/Y') }}
                                </div>

                                @if($item->description)
                                    <p style="font-size:.8rem;color:var(--text-secondary);margin-bottom:.75rem;line-height:1.4">
                                        {{ Str::limit($item->description, 65) }}
                                    </p>
                                @endif

                                {{-- Change folder dropdown --}}
                                <div style="margin-bottom:.75rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem">
                                    <span style="font-size:.72rem;color:var(--text-muted)">Dossier :</span>
                                    <form method="POST" action="{{ route('armoire.update-dossier', $item) }}" style="margin:0">
                                        @csrf
                                        @method('PATCH')
                                        <select name="dossier" class="folder-switch-select" onchange="this.form.submit()" title="Déplacer vers un autre dossier">
                                            @foreach($dossiersList as $dName)
                                                <option value="{{ $dName }}" {{ $item->dossier === $dName ? 'selected' : '' }}>
                                                    {{ $folderIcons[$dName] ?? '📁' }} {{ $dName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>

                                <div class="armoire-item-actions">
                                    {{-- Quick sell on marketplace --}}
                                    <a href="{{ route('marketplace.create', ['armoire_id' => $item->id]) }}"
                                       class="btn btn-primary btn-sm"
                                       style="flex:1;justify-content:center"
                                       title="Publier cet article sur la Marketplace">
                                        <span class="material-icons-round" style="font-size:.95rem">storefront</span>
                                        Vendre
                                    </a>

                                    {{-- Delete button --}}
                                    <form method="POST" action="{{ route('armoire.destroy', $item) }}"
                                          onsubmit="return confirm('Retirer définitivement ce vêtement de votre armoire ?')" style="margin:0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="color:var(--accent-red);padding:.4rem .6rem" title="Supprimer de l'armoire">
                                            <span class="material-icons-round" style="font-size:.95rem">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div style="margin-top:2rem;display:flex;justify-content:center">
                    {{ $items->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
function chooseDossier(dossierName, defaultCat, clickedEl = null) {
    const sel = document.getElementById('selected-dossier-input');
    if (sel) sel.value = dossierName;
    const nouv = document.getElementById('nouveau-dossier-input');
    if (nouv) nouv.value = '';

    // Highlight button
    document.querySelectorAll('.folder-option-btn').forEach(btn => {
        btn.classList.remove('selected');
        // If matches folder name, select it
        if (btn.innerText && btn.innerText.toLowerCase().includes(dossierName.toLowerCase())) {
            btn.classList.add('selected');
        }
    });

    if (clickedEl && clickedEl.classList) {
        clickedEl.classList.add('selected');
    }

    // Auto-select category
    if (defaultCat) {
        const catSelect = document.getElementById('armoire-categorie');
        if (catSelect) {
            for (let i = 0; i < catSelect.options.length; i++) {
                if (catSelect.options[i].value === defaultCat) {
                    catSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }
}

function handleCustomDossier(input) {
    if (input.value && input.value.trim() !== '') {
        document.querySelectorAll('.folder-option-btn').forEach(btn => btn.classList.remove('selected'));
    }
}

async function scanArmoireWithIA() {
    const fileInput = document.getElementById('armoire-file-input');
    if (!fileInput || !fileInput.files || !fileInput.files[0]) {
        fileInput.click();
        return;
    }

    const btn = document.getElementById('btn-scan-ia-armoire');
    const status = document.getElementById('armoire-ai-status');
    const oldText = btn.innerHTML;
    btn.innerHTML = '<span class="material-icons-round" style="font-size:1rem;vertical-align:middle">hourglass_top</span> Analyse CLIP IA en cours...';
    btn.disabled = true;

    status.style.display = 'block';
    status.style.background = 'rgba(108,99,255,.08)';
    status.style.borderColor = 'rgba(108,99,255,.25)';
    status.style.color = 'var(--primary)';
    status.innerHTML = '🤖 <strong>Diagnostic IA :</strong> Classification du textile par modèle CLIP zero-shot...';

    const formData = new FormData();
    formData.append('image', fileInput.files[0]);

    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const res = await fetch('{{ route("armoire.analyser-ia") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf ?? ''
            },
            body: formData
        });

        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || ('Erreur HTTP ' + res.status));
        }

        const data = await res.json();

        // 1. Détection de photo non-vêtement (chat, chien, animal, paysage, objet, visage...)
        if (data.is_garment === false || data.verdict === 'non_vetement') {
            status.innerHTML = `🚫 <strong>Non reconnu :</strong> ${data.message || "Cette photo n'est pas une pièce de vêtement."} (Confiance : ${data.confiance}%)<br><span style="font-size:.78rem;opacity:.95">Veuillez téléverser la photo d'un vêtement ou d'une paire de chaussures valide.</span>`;
            status.style.background = 'rgba(255,101,132,.12)';
            status.style.borderColor = 'rgba(255,101,132,.45)';
            status.style.color = '#e11d48';
            return;
        }

        // 2. Vêtement identifié avec succès
        if (data.dossier_suggere || data.nouveau_dossier) {
            const isCustom = data.is_custom_dossier || !['T-shirt', 'Pantalon', 'Chaussures', 'Chaussettes'].includes(data.dossier_suggere);

            if (isCustom) {
                // Désélectionner les 4 boutons prédéfinis
                document.querySelectorAll('.folder-option-btn').forEach(btn => btn.classList.remove('selected'));

                // Vider l'input caché pour que le nouveau dossier soit utilisé à l'enregistrement
                const selInput = document.getElementById('selected-dossier-input');
                if (selInput) selInput.value = '';

                // Remplir et mettre en surbrillance l'input "nouveau_dossier" avec la suggestion de l'IA
                const nouvInput = document.getElementById('nouveau-dossier-input');
                const folderName = data.nouveau_dossier || data.dossier_suggere;
                if (nouvInput) {
                    nouvInput.value = folderName;
                    nouvInput.style.borderColor = 'var(--primary)';
                    nouvInput.style.boxShadow = '0 0 0 3px rgba(108,99,255,.25)';
                }
            } else {
                chooseDossier(data.dossier_suggere, data.type);
            }

            // Sélectionner ou ajouter dynamiquement la catégorie suggérée
            const catSelect = document.getElementById('armoire-categorie');
            const targetCat = data.categorie || data.type;
            if (catSelect && targetCat) {
                let matched = false;
                for (let i = 0; i < catSelect.options.length; i++) {
                    if (catSelect.options[i].value.toLowerCase() === targetCat.toLowerCase()) {
                        catSelect.selectedIndex = i;
                        matched = true;
                        break;
                    }
                }
                if (!matched) {
                    const opt = document.createElement('option');
                    opt.value = targetCat;
                    opt.innerText = targetCat;
                    opt.selected = true;
                    catSelect.appendChild(opt);
                }
            }

            if (data.titre_suggere) {
                const titreEl = document.getElementById('armoire-titre');
                if (titreEl) titreEl.value = data.titre_suggere;
            }

            if (data.etat) {
                const etatSelect = document.getElementById('armoire-etat');
                if (etatSelect) {
                    for (let i = 0; i < etatSelect.options.length; i++) {
                        if (etatSelect.options[i].value === data.etat) {
                            etatSelect.selectedIndex = i;
                            break;
                        }
                    }
                }
            }

            const folderBadge = isCustom
                ? `<span class="badge" style="background:var(--primary);color:#fff;padding:3px 9px;border-radius:4px;font-weight:700">+ Nouveau dossier : ${data.nouveau_dossier || data.dossier_suggere}</span>`
                : `Dossier <strong>${data.dossier_suggere}</strong>`;

            status.innerHTML = `✨ <strong>Reconnu par l'IA :</strong> ${data.titre_suggere} (${data.confiance}%) &bull; ${folderBadge} &bull; Catégorie : <strong>${targetCat}</strong>`;
            status.style.background = 'rgba(67,217,173,.12)';
            status.style.borderColor = 'rgba(67,217,173,.5)';
            status.style.color = '#059669';
        }
    } catch (err) {
        console.error("Erreur scan IA armoire:", err);
        status.innerHTML = '⚠️ Service IA temporairement inaccessible (' + (err.message || 'erreur réseau') + '). Vous pouvez remplir les champs manuellement.';
        status.style.background = 'rgba(255,101,132,.1)';
        status.style.borderColor = 'rgba(255,101,132,.3)';
        status.style.color = 'var(--accent-orange)';
    } finally {
        btn.innerHTML = oldText;
        btn.disabled = false;
    }
}

function previewArmoireImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewWrap = document.getElementById('armoire-preview-wrap');
            const previewImg = document.getElementById('armoire-preview-img');
            previewImg.src = e.target.result;
            previewWrap.style.display = 'block';

            const dropzone = document.getElementById('armoire-dropzone');
            dropzone.querySelector('.drop-zone-text').innerText = 'Photo prête : ' + input.files[0].name;
            dropzone.querySelector('.drop-zone-hint').innerText = 'Cliquez pour remplacer la photo';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
