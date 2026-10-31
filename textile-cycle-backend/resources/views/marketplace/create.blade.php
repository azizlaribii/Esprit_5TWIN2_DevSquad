@extends('layouts.app')

@section('title', isset($article) ? 'Modifier l\'article' : 'Publier un article')
@section('meta_description', 'Publiez un vêtement à vendre ou échanger sur TexTileCycle')
@section('breadcrumb', 'Marketplace › ' . (isset($article) ? 'Modifier' : 'Publier'))

@section('styles')
<style>
.create-grid{display:grid;grid-template-columns:1fr 380px;gap:1.5rem;align-items:start}
.upload-zone{border:2px dashed var(--border);border-radius:var(--radius-lg);padding:2rem;text-align:center;cursor:pointer;transition:var(--transition);background:rgba(255,255,255,.02)}
.upload-zone:hover{border-color:var(--primary);background:rgba(108,99,255,.05)}
.upload-zone .material-icons-round{font-size:3rem;color:var(--text-muted)}
.ai-estimator{background:linear-gradient(135deg,rgba(108,99,255,.08),rgba(67,217,173,.05));border:1px solid rgba(108,99,255,.2);border-radius:var(--radius-lg);padding:1.25rem}
.ai-chip{display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .75rem;background:rgba(108,99,255,.15);border:1px solid rgba(108,99,255,.3);border-radius:100px;font-size:.78rem;font-weight:700;color:var(--primary-light);cursor:pointer;transition:var(--transition)}
.ai-chip:hover{background:rgba(108,99,255,.25)}
.price-estimate{font-family:'Outfit',sans-serif;font-size:2rem;font-weight:800;background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
@media(max-width:900px){.create-grid{grid-template-columns:1fr}}
</style>
@endsection

@section('content')
<div class="animate-fade-in-up">
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem">
        <a href="{{ route('marketplace.index') }}" class="btn btn-secondary btn-sm">
            <span class="material-icons-round" style="font-size:1rem">arrow_back</span> Retour
        </a>
        <div>
            <h1 class="page-title">{{ isset($article) ? 'Modifier l\'article' : 'Publier un article' }}</h1>
            <p class="page-subtitle">{{ isset($article) ? 'Mettez à jour les informations' : 'Ajoutez un vêtement à la marketplace circulaire' }}</p>
        </div>
    </div>

    <form method="POST"
          action="{{ isset($article) ? route('marketplace.update', $article) : route('marketplace.store') }}"
          enctype="multipart/form-data"
          id="article-form">
        @csrf
        @if(isset($article)) @method('PUT') @endif

        {{-- ─── Global validation error alert ─── --}}
        @if($errors->any())
            <div style="background:rgba(255,101,132,.12);border:1px solid rgba(255,101,132,.35);border-radius:var(--radius-md);padding:1rem 1.25rem;margin-bottom:1rem;display:flex;gap:.75rem;align-items:flex-start">
                <span class="material-icons-round" style="color:var(--accent-red);flex-shrink:0;margin-top:.1rem">error</span>
                <div>
                    <div style="font-weight:700;color:var(--accent-red);font-size:.9rem">Veuillez corriger les erreurs ci-dessous :</div>
                    <ul style="margin:.35rem 0 0 1rem;padding:0;font-size:.83rem;color:var(--text-secondary)">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif


        <div class="create-grid">

            {{-- ─── FORM LEFT ─── --}}
            <div>
                {{-- Image Upload / Armoire Selection --}}
                <div class="card section">
                    <div class="card-header" style="flex-wrap:wrap;gap:.5rem">
                        <div class="card-title">
                            <span class="material-icons-round" style="color:var(--primary);font-size:1.1rem">add_photo_alternate</span>
                            Photo du vêtement
                        </div>
                        {{-- Tabs switcher --}}
                        <div style="display:flex;gap:.35rem;background:rgba(255,255,255,.05);padding:.25rem;border-radius:var(--radius-sm);border:1px solid var(--border)">
                            <button type="button" class="btn btn-sm" id="tab-btn-device"
                                    style="background:var(--primary);color:white;padding:.3rem .75rem;font-size:.78rem;border-radius:var(--radius-xs);border:none;cursor:pointer"
                                    onclick="switchPhotoTab('device')">
                                <span class="material-icons-round" style="font-size:.9rem">upload_file</span> Mon Appareil
                            </button>
                            <button type="button" class="btn btn-sm" id="tab-btn-armoire"
                                    style="background:transparent;color:var(--text-secondary);padding:.3rem .75rem;font-size:.78rem;border-radius:var(--radius-xs);border:none;cursor:pointer"
                                    onclick="switchPhotoTab('armoire')">
                                <span class="material-icons-round" style="font-size:.9rem">checkroom</span> Mon Armoire ({{ isset($armoireItems) ? $armoireItems->count() : 0 }})
                            </button>
                        </div>
                    </div>

                    {{-- Active Photo Preview (from Armoire or Upload) --}}
                    <div id="active-photo-preview" style="text-align:center;padding:1.25rem;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);margin-bottom:1.25rem;display:{{ (isset($selectedArmoire) || old('armoire_image') || (isset($article) && $article->image_url)) ? 'block' : 'none' }}">
                        <div style="position:relative;display:inline-block">
                            <img id="main-active-img" src="{{ isset($selectedArmoire) ? $selectedArmoire->display_image_url : (isset($article) ? $article->display_image_url : '') }}"
                                 style="max-height:220px;max-width:100%;border-radius:var(--radius-md);object-fit:cover;box-shadow:0 6px 20px rgba(0,0,0,.08);border:2px solid var(--primary)" alt="Photo vêtement">
                            <span id="main-active-badge" style="position:absolute;top:8px;right:8px;background:var(--secondary);color:#0a0e1a;font-weight:800;font-size:.72rem;padding:.25rem .65rem;border-radius:100px;box-shadow:0 2px 6px rgba(0,0,0,.15)">
                                {{ isset($selectedArmoire) ? '✓ Photo de votre armoire' : 'Photo active' }}
                            </span>
                        </div>
                        <div style="font-size:.82rem;font-weight:600;color:var(--text-primary);margin-top:.6rem" id="active-photo-title">
                            {{ isset($selectedArmoire) ? $selectedArmoire->titre : 'Photo prête pour publication' }}
                        </div>
                    </div>

                    {{-- Hidden inputs for armoire selection --}}
                    <input type="hidden" name="armoire_image" id="armoire_image_input" value="{{ old('armoire_image', $selectedArmoire->image_path ?? '') }}">
                    <input type="hidden" name="armoire_vetement_id" id="armoire_vetement_id_input" value="{{ old('armoire_vetement_id', $selectedArmoire->id ?? '') }}">

                    {{-- TAB 1: File upload from device (existing method) --}}
                    <div id="pane-device" style="{{ (isset($selectedArmoire) || old('armoire_image')) ? 'display:none' : 'display:block' }}">
                        <div class="upload-zone" id="upload-zone" onclick="document.getElementById('image-input').click()">
                            <span class="material-icons-round">cloud_upload</span>
                            <div style="margin-top:.75rem;font-weight:600;color:var(--text-secondary)">Cliquez ou glissez une photo</div>
                            <div style="font-size:.8rem;color:var(--text-muted);margin-top:.35rem">JPG, PNG, WebP — max 5 MB</div>
                            @if(isset($article) && $article->image_url)
                                <img src="{{ $article->display_image_url }}" style="max-height:150px;margin-top:1rem;border-radius:var(--radius-md)" alt="aperçu" onerror="this.src='/images/default-clothing.jpg'">
                            @endif
                            <div id="image-preview" style="margin-top:1rem"></div>
                        </div>
                        <input type="file" name="image" id="image-input" accept="image/*" style="display:none" onchange="previewImage(this)">
                        @error('image') <div class="form-error"><span class="material-icons-round" style="font-size:.9rem">error</span> {{ $message }}</div> @enderror
                    </div>

                    {{-- TAB 2: Select from Armoire --}}
                    <div id="pane-armoire" style="{{ (isset($selectedArmoire) || old('armoire_image')) ? 'display:block' : 'display:none' }}">
                        @if(isset($armoireItems) && $armoireItems->count() > 0)
                            <div style="margin-bottom:.5rem;font-size:.82rem;color:var(--text-secondary);display:flex;align-items:center;justify-content:space-between">
                                <span>Sélectionnez un vêtement depuis votre <strong>Armoire</strong> :</span>
                                <a href="{{ route('armoire.index') }}" target="_blank" style="color:var(--primary-light);font-size:.78rem;text-decoration:none">
                                    + Gérer mon Armoire ↗
                                </a>
                            </div>

                            {{-- Folder filter pills --}}
                            <div style="display:flex;gap:.35rem;overflow-x:auto;padding-bottom:.4rem;margin-bottom:.65rem">
                                <button type="button" class="btn btn-secondary btn-sm armoire-folder-pill active" style="font-size:.72rem;padding:.2rem .55rem" onclick="filterArmoireByFolder('all', this)">Tous</button>
                                <button type="button" class="btn btn-secondary btn-sm armoire-folder-pill" style="font-size:.72rem;padding:.2rem .55rem" onclick="filterArmoireByFolder('T-shirt', this)">👕 T-shirts</button>
                                <button type="button" class="btn btn-secondary btn-sm armoire-folder-pill" style="font-size:.72rem;padding:.2rem .55rem" onclick="filterArmoireByFolder('Pantalon', this)">👖 Pantalons</button>
                                <button type="button" class="btn btn-secondary btn-sm armoire-folder-pill" style="font-size:.72rem;padding:.2rem .55rem" onclick="filterArmoireByFolder('Chaussures', this)">👟 Chaussures</button>
                                <button type="button" class="btn btn-secondary btn-sm armoire-folder-pill" style="font-size:.72rem;padding:.2rem .55rem" onclick="filterArmoireByFolder('Chaussettes', this)">🧦 Chaussettes</button>
                            </div>

                            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(130px, 1fr));gap:.75rem;max-height:280px;overflow-y:auto;padding:.25rem" id="armoire-picker-grid">
                                @foreach($armoireItems as $item)
                                    @php
                                        $isSelected = (isset($selectedArmoire) && $selectedArmoire->id == $item->id) || old('armoire_vetement_id') == $item->id;
                                    @endphp
                                    <div class="armoire-select-card {{ $isSelected ? 'selected' : '' }}"
                                         id="armoire-card-{{ $item->id }}"
                                         data-dossier="{{ $item->dossier ?? 'T-shirt' }}"
                                         onclick="selectArmoireItem({{ $item->id }}, '{{ addslashes($item->image_path) }}', '{{ addslashes($item->display_image_url) }}', '{{ addslashes($item->titre ?? '') }}', '{{ addslashes($item->categorie ?? '') }}', '{{ addslashes($item->marque ?? '') }}', '{{ addslashes($item->taille ?? '') }}', '{{ addslashes($item->genre ?? '') }}', '{{ addslashes($item->etat ?? '') }}', '{{ addslashes($item->description ?? '') }}')"
                                         style="border:2px solid {{ $isSelected ? 'var(--primary)' : 'var(--border)' }};border-radius:var(--radius-sm);overflow:hidden;background:rgba(255,255,255,.02);cursor:pointer;transition:var(--transition);position:relative">
                                        <div style="height:100px;background:rgba(0,0,0,.2);overflow:hidden;position:relative">
                                            <img src="{{ $item->display_image_url }}" alt="{{ $item->titre }}" style="width:100%;height:100%;object-fit:cover" onerror="this.src='/images/default-clothing.jpg'">
                                            <span class="armoire-badge-check" style="position:absolute;top:4px;right:4px;background:var(--primary);color:white;border-radius:50%;width:20px;height:20px;display:{{ $isSelected ? 'flex' : 'none' }};align-items:center;justify-content:center;font-size:.75rem">✓</span>
                                            <span style="position:absolute;bottom:4px;left:4px;background:rgba(0,0,0,.7);font-size:.65rem;padding:.1rem .35rem;border-radius:4px;color:white">
                                                {{ $item->dossier_icon }} {{ $item->dossier ?? 'T-shirt' }}
                                            </span>
                                        </div>
                                        <div style="padding:.45rem;font-size:.75rem">
                                            <div style="font-weight:700;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $item->titre }}</div>
                                            <div style="color:var(--text-muted);font-size:.7rem">
                                                {{ $item->categorie ?? 'Vêtement' }}
                                                @if(!empty($item->marque)) • {{ $item->marque }} @endif
                                                • {{ $item->taille ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="text-align:center;padding:1.5rem;background:rgba(255,255,255,.02);border-radius:var(--radius-md);border:1px dashed var(--border)">
                                <span class="material-icons-round" style="font-size:2rem;color:var(--text-muted)">checkroom</span>
                                <p style="font-size:.85rem;color:var(--text-secondary);margin:.5rem 0">Votre armoire ne contient pas encore de photos.</p>
                                <a href="{{ route('armoire.index') }}" class="btn btn-secondary btn-sm" style="display:inline-flex">
                                    <span class="material-icons-round" style="font-size:.9rem">add_photo_alternate</span> Déposer des photos dans mon Armoire
                                </a>
                            </div>
                        @endif
                        <div id="armoire-selected-notice" style="margin-top:.75rem;padding:.5rem .75rem;border-radius:var(--radius-sm);background:rgba(108,99,255,.1);border:1px solid rgba(108,99,255,.3);display:{{ (isset($selectedArmoire) || old('armoire_image')) ? 'block' : 'none' }};font-size:.8rem;color:var(--primary-light)">
                            👗 Photo de l'Armoire sélectionnée : <strong id="armoire-selected-name">{{ $selectedArmoire->titre ?? 'Vêtement sélectionné' }}</strong>
                        </div>
                    </div>

                    <p style="font-size:.78rem;color:var(--text-muted);margin-top:.75rem;display:flex;align-items:center;gap:.35rem">
                        <span class="material-icons-round" style="font-size:.9rem">auto_awesome</span>
                        <strong style="color:var(--primary-light)">IA :</strong> L'IA analysera automatiquement la photo pour classifier et estimer le prix
                    </p>
                </div>

                {{-- Informations principales --}}
                <div class="card section">
                    <div class="card-header">
                        <div class="card-title">
                            <span class="material-icons-round" style="color:var(--primary);font-size:1.1rem">info</span>
                            Informations principales
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="titre">Titre de l'annonce *</label>
                        <input type="text" name="titre" id="titre" class="form-control {{ $errors->has('titre') ? 'is-invalid' : '' }}"
                               value="{{ old('titre', $article->titre ?? '') }}" placeholder="Ex: Jean slim H&M bleu indigo taille 40" required>
                        @error('titre') <div class="form-error"><span class="material-icons-round" style="font-size:.9rem">error</span> {{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Description *</label>
                        <textarea name="description" id="description" class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                  placeholder="Décrivez le vêtement : état, marque, historique, défauts éventuels…" required>{{ old('description', $article->description ?? '') }}</textarea>
                        @error('description') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label" for="categorie">Catégorie *</label>
                            <select name="categorie" id="categorie" class="form-control {{ $errors->has('categorie') ? 'is-invalid' : '' }}" required>
                                <option value="">Sélectionner…</option>
                                @foreach(['T-Shirts & Tops','Jeans & Pantalons','Vestes & Manteaux','Robes & Jupes','Chaussures','Accessoires','Sportswear','Lingerie'] as $cat)
                                    <option value="{{ $cat }}" {{ old('categorie', $article->categorie ?? '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                            @error('categorie') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="marque">Marque</label>
                            <input type="text" name="marque" id="marque" class="form-control"
                                   value="{{ old('marque', $article->marque ?? '') }}" placeholder="H&M, Zara, Nike…">
                        </div>
                    </div>

                    <div class="grid-3">
                        <div class="form-group">
                            <label class="form-label" for="taille">Taille *</label>
                            <select name="taille" id="taille" class="form-control" required>
                                <option value="">Sélectionner…</option>
                                @foreach(['XS','S','M','L','XL','XXL','36','38','40','42','44','46'] as $t)
                                    <option value="{{ $t }}" {{ old('taille', $article->taille ?? '') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="genre">Genre *</label>
                            <select name="genre" id="genre" class="form-control" required>
                                <option value="">Sélectionner…</option>
                                @foreach(['Femme','Homme','Enfant','Unisexe'] as $g)
                                    <option value="{{ $g }}" {{ old('genre', $article->genre ?? '') == $g ? 'selected' : '' }}>{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="etat">État *</label>
                            <select name="etat" id="etat" class="form-control" required oninput="estimatePrice()">
                                <option value="">Sélectionner…</option>
                                @foreach(['Neuf avec étiquette','Très bon état','Bon état','État correct'] as $e)
                                    <option value="{{ $e }}" {{ old('etat', $article->etat ?? '') == $e ? 'selected' : '' }}>{{ $e }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="type">Type de transaction *</label>
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.75rem">
                            @foreach(['vente' => ['💰','Vente','primary'], 'echange' => ['🔄','Échange','warning']] as $val => $info)
                                <label style="cursor:pointer">
                                    <input type="radio" name="type" value="{{ $val }}" {{ old('type', $article->type ?? 'vente') == $val ? 'checked' : '' }} style="display:none" onchange="togglePriceField()">
                                    <div class="type-option badge-{{ $info[2] }}" style="padding:.75rem;border-radius:var(--radius-md);text-align:center;border:2px solid transparent;transition:var(--transition);cursor:pointer" onclick="selectType('{{ $val }}')">
                                        <div style="font-size:1.5rem">{{ $info[0] }}</div>
                                        <div style="font-size:.85rem;font-weight:700;margin-top:.25rem">{{ $info[1] }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── FORM RIGHT ─── --}}
            <div>
                {{-- AI Price Estimator --}}
                <div class="ai-estimator section">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem">
                        <span class="material-icons-round" style="color:var(--primary-light)">psychology</span>
                        <div style="font-weight:700;color:var(--text-primary)">Estimation IA du prix</div>
                        <span class="ai-badge" style="margin-left:auto;font-size:.65rem">IA</span>
                    </div>
                    <div id="ai-estimate-display" style="margin-bottom:.75rem">
                        <div class="price-estimate" id="ai-price">25 – 45 DT</div>
                        <div style="font-size:.8rem;color:var(--text-muted)">Prix indicatif basé sur le type, l'état et la marque</div>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:.75rem">
                        <span class="ai-chip" onclick="applyAiPrice(35)">Appliquer 35 DT</span>
                        <span class="ai-chip" onclick="applyAiPrice(45)">Appliquer 45 DT</span>
                        <span class="ai-chip" onclick="applyAiPrice(25)">Prix bas 25 DT</span>
                    </div>
                    <div style="font-size:.75rem;color:var(--text-muted)">
                        Facteurs analysés : catégorie, marque, état, ancienneté, demande actuelle
                    </div>
                </div>

                {{-- Prix --}}
                <div class="card section" id="price-section">
                    <div class="card-header">
                        <div class="card-title">
                            <span class="material-icons-round" style="color:var(--accent-orange);font-size:1.1rem">euro</span>
                            Prix de vente
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prix">Prix (DT)</label>
                        <div style="position:relative">
                            <span style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:700">DT</span>
                            <input type="number" name="prix" id="prix" class="form-control {{ $errors->has('prix') ? 'is-invalid' : '' }}"
                                   value="{{ old('prix', $article->prix ?? '') }}" min="0" step="0.01" placeholder="0.00" style="padding-left:2rem">
                        </div>
                        @error('prix') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="article_echange">Article souhaité en échange</label>
                        <input type="text" name="article_echange" id="article_echange" class="form-control"
                               value="{{ old('article_echange', $article->article_echange ?? '') }}" placeholder="Ex: Veste M en bon état">
                    </div>
                </div>

                {{-- Publier --}}
                <div class="card">
                    <div style="display:flex;flex-direction:column;gap:.75rem">
                        <button type="submit" class="btn btn-primary" id="btn-submit" style="width:100%;justify-content:center;padding:.875rem">
                            <span class="material-icons-round">{{ isset($article) ? 'save' : 'publish' }}</span>
                            {{ isset($article) ? 'Enregistrer les modifications' : 'Publier l\'annonce' }}
                        </button>
                        @if(isset($article))
                            <form method="POST" action="{{ route('marketplace.destroy', $article) }}" onsubmit="return confirm('Supprimer définitivement cet article ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center" id="btn-delete">
                                    <span class="material-icons-round">delete</span> Supprimer l'annonce
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('marketplace.index') }}" class="btn btn-secondary" style="width:100%;justify-content:center">
                            Annuler
                        </a>
                    </div>
                    <p style="font-size:.75rem;color:var(--text-muted);margin-top:1rem;text-align:center">
                        <span class="material-icons-round" style="font-size:.9rem">eco</span>
                        Chaque article vendu = 1 point éco-impact pour votre profil
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function switchPhotoTab(tab) {
    const paneDevice = document.getElementById('pane-device');
    const paneArmoire = document.getElementById('pane-armoire');
    const btnDevice = document.getElementById('tab-btn-device');
    const btnArmoire = document.getElementById('tab-btn-armoire');

    if (tab === 'device') {
        paneDevice.style.display = 'block';
        paneArmoire.style.display = 'none';
        btnDevice.style.background = 'var(--primary)';
        btnDevice.style.color = 'white';
        btnArmoire.style.background = 'transparent';
        btnArmoire.style.color = 'var(--text-secondary)';
        // Clear armoire selection if user deliberately switches to device file upload
        document.getElementById('armoire_image_input').value = '';
        document.getElementById('armoire_vetement_id_input').value = '';
        document.getElementById('armoire-selected-notice').style.display = 'none';
        document.querySelectorAll('.armoire-select-card').forEach(c => {
            c.style.borderColor = 'var(--border)';
            const check = c.querySelector('.armoire-badge-check');
            if (check) check.style.display = 'none';
        });
    } else {
        paneDevice.style.display = 'none';
        paneArmoire.style.display = 'block';
        btnArmoire.style.background = 'var(--primary)';
        btnArmoire.style.color = 'white';
        btnDevice.style.background = 'transparent';
        btnDevice.style.color = 'var(--text-secondary)';
        // Clear file input
        const fileInput = document.getElementById('image-input');
        if (fileInput) fileInput.value = '';
        const preview = document.getElementById('image-preview');
        if (preview) preview.innerHTML = '';
    }
}

function filterArmoireByFolder(folder, btn) {
    document.querySelectorAll('.armoire-folder-pill').forEach(b => {
        b.classList.remove('active');
        b.style.background = 'rgba(255,255,255,.05)';
        b.style.borderColor = 'var(--border)';
        b.style.color = 'var(--text-secondary)';
    });
    btn.classList.add('active');
    btn.style.background = 'var(--primary)';
    btn.style.borderColor = 'var(--primary)';
    btn.style.color = 'white';

    document.querySelectorAll('.armoire-select-card').forEach(card => {
        const itemDossier = card.dataset.dossier;
        if (folder === 'all' || itemDossier === folder) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function selectArmoireItem(id, imagePath, imageUrl, titre, cat, marque, taille, genre, etat, desc) {
    // Highlight selected card
    document.querySelectorAll('.armoire-select-card').forEach(c => {
        c.style.borderColor = 'var(--border)';
        const check = c.querySelector('.armoire-badge-check');
        if (check) check.style.display = 'none';
    });

    const card = document.getElementById('armoire-card-' + id);
    if (card) {
        card.style.borderColor = 'var(--primary)';
        const check = card.querySelector('.armoire-badge-check');
        if (check) check.style.display = 'flex';
    }

    // Set hidden inputs
    document.getElementById('armoire_image_input').value = imagePath;
    document.getElementById('armoire_vetement_id_input').value = id;

    // Show selected notice
    const notice = document.getElementById('armoire-selected-notice');
    const nameSpan = document.getElementById('armoire-selected-name');
    nameSpan.innerText = titre || 'Vêtement sélectionné';
    notice.style.display = 'block';

    // Remplissage automatique des champs :
    // 1. Titre de l'annonce
    const titreInput = document.getElementById('titre');
    if (titreInput) {
        titreInput.value = titre || '';
    }

    // 2. Description
    const descInput = document.getElementById('description');
    if (descInput) {
        descInput.value = desc || '';
    }

    // 3. Catégorie
    const catInput = document.getElementById('categorie');
    if (catInput) {
        catInput.value = cat || '';
    }

    // 4. Marque
    const marqueInput = document.getElementById('marque');
    if (marqueInput) {
        marqueInput.value = marque || '';
    }

    // 5. Taille
    const tailleInput = document.getElementById('taille');
    if (tailleInput) {
        tailleInput.value = taille || '';
    }

    // 6. Genre
    const genreInput = document.getElementById('genre');
    if (genreInput) {
        genreInput.value = genre || '';
    }

    // 7. État
    const etatInput = document.getElementById('etat');
    if (etatInput) {
        etatInput.value = etat || '';
        estimatePrice();
    }
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('image-preview').innerHTML =
                '<img src="' + e.target.result + '" style="max-height:200px;border-radius:var(--radius-md);margin-top:.5rem" alt="aperçu">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function togglePriceField() {
    const type = document.querySelector('input[name="type"]:checked')?.value;
    const section = document.getElementById('price-section');
    if (section) section.style.opacity = type === 'echange' ? '0.6' : '1';
}
function selectType(val) {
    document.querySelectorAll('input[name="type"]').forEach(r => r.checked = (r.value === val));
    togglePriceField();
}
function estimatePrice() {
    const etat = document.getElementById('etat').value;
    const prices = {'Neuf avec étiquette':'40 – 80 DT','Très bon état':'20 – 45 DT','Bon état':'10 – 25 DT','État correct':'5 – 15 DT'};
    document.getElementById('ai-price').textContent = prices[etat] || '15 – 35 DT';
}
function applyAiPrice(p) { document.getElementById('prix').value = p; }
togglePriceField();

@if(isset($selectedArmoire) && !old('titre'))
document.addEventListener('DOMContentLoaded', function() {
    selectArmoireItem(
        {{ $selectedArmoire->id }},
        '{{ addslashes($selectedArmoire->image_path) }}',
        '{{ addslashes($selectedArmoire->display_image_url) }}',
        '{{ addslashes($selectedArmoire->titre ?? '') }}',
        '{{ addslashes($selectedArmoire->categorie ?? '') }}',
        '{{ addslashes($selectedArmoire->marque ?? '') }}',
        '{{ addslashes($selectedArmoire->taille ?? '') }}',
        '{{ addslashes($selectedArmoire->genre ?? '') }}',
        '{{ addslashes($selectedArmoire->etat ?? '') }}',
        '{{ addslashes($selectedArmoire->description ?? '') }}'
    );
});
@endif
async function genererDescriptionIA() {
    const btn = document.getElementById('btn-ia-desc');
    const oldText = btn.innerHTML;
    btn.innerHTML = '<span class="material-icons-round" style="font-size:.9rem">hourglass_top</span> Rédaction IA...';
    btn.disabled = true;

    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const res = await fetch('{{ route("marketplace.ameliorer-ia") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf ?? ''
            },
            body: JSON.stringify({
                titre: document.getElementById('titre')?.value,
                categorie: document.getElementById('categorie')?.value,
                marque: document.getElementById('marque')?.value,
                etat: document.getElementById('etat')?.value
            })
        });

        const data = await res.json();
        if (data.description) {
            document.getElementById('description').value = data.description;
        }
    } catch(e) {
        console.error(e);
    } finally {
        btn.innerHTML = oldText;
        btn.disabled = false;
    }
}
</script>
@endsection
