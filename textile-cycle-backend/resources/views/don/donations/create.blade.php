@extends('layouts.app')

@section('title', 'Faire un don intelligent')
@section('meta_description', 'Faites un don de vêtements : notre IA analyse vos photos et vous propose les associations les plus adaptées.')
@section('breadcrumb', 'Gestion › Dons › Nouveau don')

@section('styles')
<style>
    .form-step {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        margin-bottom: 1.25rem;
        transition: var(--transition);
    }
    .form-step:hover { border-color: rgba(108,99,255,.25); }
    .step-header {
        display: flex;
        align-items: center;
        gap: .875rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border);
    }
    .step-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        font-size: 1.25rem;
    }
    .step-icon.purple { background: rgba(108,99,255,.15); color: var(--primary-light); }
    .step-icon.green  { background: rgba(67,217,173,.15);  color: var(--secondary); }
    .step-icon.blue   { background: rgba(41,182,246,.15);  color: var(--accent-blue); }
    .step-icon.orange { background: rgba(255,167,38,.15);  color: var(--accent-orange); }
    .step-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); }
    .step-subtitle { font-size: .8rem; color: var(--text-muted); margin-top: .1rem; }

    /* Photo dropzone */
    .photo-dropzone {
        border: 2px dashed rgba(108,99,255,.35);
        border-radius: var(--radius-md);
        padding: 2.5rem 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: var(--transition);
        background: rgba(108,99,255,.03);
        position: relative;
    }
    .photo-dropzone:hover, .photo-dropzone.drag-over {
        border-color: var(--primary);
        background: rgba(108,99,255,.08);
    }
    .photo-dropzone input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }

    /* Grid fields */
    .fields-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .fields-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
    @media(max-width: 700px) {
        .fields-grid, .fields-grid-3 { grid-template-columns: 1fr; }
    }

    /* Association card selector */
    .assoc-search-box {
        position: relative; margin-bottom: .75rem;
    }
    .assoc-search-box .material-icons-round {
        position: absolute; left: .75rem; top: 50%; transform: translateY(-50%);
        font-size: 1rem; color: var(--text-muted); pointer-events: none;
    }
    .assoc-search-input {
        width: 100%; padding: .65rem 1rem .65rem 2.5rem;
        background: rgba(255,255,255,.04); border: 1px solid var(--border);
        border-radius: var(--radius-sm); color: var(--text-primary);
        font-size: .875rem; font-family: 'Inter', sans-serif;
        transition: var(--transition);
    }
    .assoc-search-input:focus {
        outline: none; border-color: var(--primary);
        background: rgba(108,99,255,.05);
        box-shadow: 0 0 0 3px rgba(108,99,255,.1);
    }
    .assoc-list {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: .75rem;
        max-height: 320px; overflow-y: auto; padding-right: .25rem;
    }
    .assoc-card {
        border: 1.5px solid var(--border); border-radius: var(--radius-md);
        padding: .875rem; cursor: pointer; transition: var(--transition);
        background: rgba(255,255,255,.02); position: relative;
    }
    .assoc-card:hover { border-color: var(--primary); background: rgba(108,99,255,.07); }
    .assoc-card.selected { border-color: var(--secondary); background: rgba(67,217,173,.08); }
    .assoc-card input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
    .assoc-card-check {
        position: absolute; top: .5rem; right: .5rem;
        width: 20px; height: 20px; border-radius: 50%;
        border: 2px solid var(--border); background: transparent;
        display: flex; align-items: center; justify-content: center;
        transition: var(--transition);
    }
    .assoc-card.selected .assoc-card-check {
        background: var(--secondary); border-color: var(--secondary);
    }
    .assoc-card-name { font-size: .875rem; font-weight: 600; color: var(--text-primary); padding-right: 1.5rem; }
    .assoc-card-city { font-size: .75rem; color: var(--text-muted); margin-top: .25rem;
        display: flex; align-items: center; gap: .2rem; }

    /* AI badge */
    .ai-hint {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .2rem .625rem; border-radius: 100px;
        background: linear-gradient(135deg, rgba(108,99,255,.15), rgba(67,217,173,.08));
        border: 1px solid rgba(108,99,255,.3); font-size: .7rem; font-weight: 700;
        color: var(--primary-light); text-transform: uppercase; letter-spacing: .5px;
        margin-bottom: 1rem;
    }

    /* In-page Modal */
    .modal-overlay {
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(8px);
        display: none; align-items: center; justify-content: center;
        padding: 1rem;
    }
    .modal-overlay.active { display: flex; animation: modalFadeIn .2s ease-out; }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(.98); }
        to { opacity: 1; transform: scale(1); }
    }
    .modal-box {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        width: 100%; max-width: 520px;
        box-shadow: 0 25px 60px rgba(0,0,0,.6);
        max-height: 90vh; overflow-y: auto;
    }
    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .modal-body { padding: 1.5rem; }
    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border);
        display: flex; align-items: center; justify-content: flex-end; gap: .75rem;
    }

    /* Floating Toast */
    .toast-popup {
        position: fixed; bottom: 2rem; right: 2rem; z-index: 10000;
        background: #0f172a; border: 1px solid var(--secondary);
        color: var(--text-primary); border-radius: var(--radius-md);
        padding: .85rem 1.25rem; font-size: .875rem; display: flex; align-items: center; gap: .6rem;
        box-shadow: 0 10px 30px rgba(0,0,0,.4);
        transform: translateY(100px); opacity: 0; pointer-events: none;
        transition: all .3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .toast-popup.show {
        transform: translateY(0); opacity: 1; pointer-events: auto;
    }
</style>
@endsection

@section('content')
<div class="animate-fade-in-up">

    {{-- Page header --}}
    <div style="margin-bottom:1.75rem">
        <a href="{{ route('dons.index') }}"
           style="color:var(--text-muted);font-size:.85rem;display:inline-flex;align-items:center;gap:.3rem;text-decoration:none;margin-bottom:.875rem;transition:var(--transition)"
           onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
            <span class="material-icons-round" style="font-size:1rem">arrow_back</span>
            Retour à l'historique des dons
        </a>
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
            <h1 class="page-title" style="margin-bottom:0">Faire un don intelligent</h1>
            <span class="ai-badge">✦ Analyse IA</span>
        </div>
        <p class="page-subtitle" style="margin-top:.5rem">
            Nos algorithmes analysent vos photos pour identifier le vêtement et suggèrent les associations
            partenaires les mieux adaptées à votre don.
        </p>
    </div>

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:1.5rem">
            <span class="material-icons-round">error_outline</span>
            <div>
                <strong>Veuillez corriger les erreurs :</strong>
                <ul style="margin:.35rem 0 0;padding-left:1.25rem">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <form novalidate method="POST" action="{{ route('donations.store') }}" enctype="multipart/form-data" id="donation-form">
    @csrf

    {{-- ── ÉTAPE 1 : Photos ──────────────────────────────────────────────── --}}
    <div class="form-step">
        <div class="step-header">
            <div class="step-icon purple">
                <span class="material-icons-round">photo_camera</span>
            </div>
            <div>
                <div class="step-title">1 — Photos du vêtement</div>
                <div class="step-subtitle">L'IA déduira automatiquement la catégorie, la taille et l'état</div>
            </div>
        </div>

        <div class="photo-dropzone" id="dropzone">
            <input id="photos" name="photos[]" type="file" multiple required
                   accept="image/jpeg,image/png,image/webp"
                   onchange="handleFiles(this.files)">
            <span class="material-icons-round" style="font-size:2.5rem;color:var(--primary-light);display:block;margin-bottom:.75rem">cloud_upload</span>
            <p style="font-size:.95rem;font-weight:600;color:var(--text-primary);margin-bottom:.35rem">
                Glissez vos photos ici ou cliquez pour sélectionner
            </p>
            <p style="font-size:.8rem;color:var(--text-muted)">
                JPG, PNG, WebP · {{ round(config('textilecycle.max_photo_kb') / 1024) }} Mo max · {{ config('textilecycle.max_photos') }} photos max
            </p>
        </div>

        {{-- Preview --}}
        <div id="photo-preview" style="display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1rem"></div>
        @include('don.partials.field-error', ['name' => 'photos'])
        @foreach ($errors->get('photos.*') as $photoErrors)
            @foreach ($photoErrors as $photoError)
                <p style="color:var(--accent-red,#dc2626);font-size:.78rem;margin:.25rem 0 0">{{ $photoError }}</p>
            @endforeach
        @endforeach
    </div>

    {{-- ── ÉTAPE 2 : Informations de base ────────────────────────────────── --}}
    <div class="form-step">
        <div class="step-header">
            <div class="step-icon green">
                <span class="material-icons-round">edit_note</span>
            </div>
            <div>
                <div class="step-title">2 — Informations de base</div>
                <div class="step-subtitle">Titre, description et localisation</div>
            </div>
        </div>

        <div style="display:grid;gap:1rem">
            <div>
                <label class="form-label" for="title">Titre du don <span style="color:var(--accent-red)">*</span></label>
                <input id="title" name="title" type="text" required maxlength="120" class="form-control"
                       placeholder="Ex. Manteau d'hiver enfant + 2 pulls laine"
                       value="{{ old('title') }}">
                @include('don.partials.field-error', ['name' => 'title'])
            </div>

            <div>
                <label class="form-label" for="description">Description <span style="color:var(--text-muted);font-weight:400">(facultatif)</span></label>
                <textarea id="description" name="description" rows="3" maxlength="2000" class="form-control"
                          placeholder="Marque, matière, défauts éventuels, raison du don…">{{ old('description') }}</textarea>
                @include('don.partials.field-error', ['name' => 'description'])
            </div>

            <div class="fields-grid">
                <div>
                    <label class="form-label" for="quantity">Nombre de pièces <span style="color:var(--accent-red)">*</span></label>
                    <input id="quantity" name="quantity" type="number" min="1" max="200" required
                           class="form-control" value="{{ old('quantity', 1) }}">
                    @include('don.partials.field-error', ['name' => 'quantity'])
                </div>
                <div>
                    <label class="form-label" for="city">Ville <span style="color:var(--accent-red)">*</span></label>
                    <input id="city" name="city" type="text" required maxlength="100"
                           class="form-control" placeholder="Tunis" value="{{ old('city') }}">
                    @include('don.partials.field-error', ['name' => 'city'])
                    <input type="hidden" id="lat" name="lat" value="{{ old('lat') }}">
                    <input type="hidden" id="lng" name="lng" value="{{ old('lng') }}">
                    <button type="button" id="use-location"
                            style="margin-top:.4rem;font-size:.75rem;color:var(--primary-light);background:none;border:none;cursor:pointer;padding:0;display:flex;align-items:center;gap:.25rem">
                        <span class="material-icons-round" style="font-size:.9rem">my_location</span>
                        Utiliser ma position GPS
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ÉTAPE 3 : Caractéristiques textile ────────────────────────────── --}}
    <div class="form-step">
        <div class="step-header">
            <div class="step-icon blue">
                <span class="material-icons-round">checkroom</span>
            </div>
            <div>
                <div class="step-title">3 — Caractéristiques textile</div>
                <div class="step-subtitle">Laissez vide pour que l'IA les déduise de vos photos</div>
            </div>
        </div>

        <span class="ai-hint">
            <span class="material-icons-round" style="font-size:.85rem">auto_awesome</span>
            Champs optionnels — l'IA complète ce qui manque
        </span>

        @php use App\Support\Textile; @endphp

        <div class="fields-grid-3">
            @foreach([
                ['category',  'Catégorie',  Textile::CATEGORIES],
                ['condition', 'État',       Textile::CONDITIONS],
                ['age_group', 'Public',     Textile::AGE_GROUPS],
                ['gender',    'Genre',      Textile::GENDERS],
                ['season',    'Saison',     Textile::SEASONS],
            ] as [$field, $label, $options])
            <div>
                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                <select id="{{ $field }}" name="{{ $field }}" class="form-control">
                    <option value="">— IA décidera —</option>
                    @foreach($options as $key => $optLabel)
                        <option value="{{ $key }}" @selected(old($field) === $key)>{{ $optLabel }}</option>
                    @endforeach
                </select>
                @include('don.partials.field-error', ['name' => $field])
            </div>
            @endforeach

            <div>
                <label class="form-label" for="size">Taille</label>
                <input id="size" name="size" type="text" list="sizes" maxlength="20"
                       class="form-control" placeholder="M, 5-6a, 42…" value="{{ old('size') }}">
                @include('don.partials.field-error', ['name' => 'size'])
                <datalist id="sizes">
                    @foreach(Textile::SIZE_ORDER as $s)<option value="{{ $s }}">@endforeach
                </datalist>
            </div>
        </div>
    </div>

    {{-- ── ÉTAPE 4 : Association destinataire ────────────────────────────── --}}
    <div class="form-step" id="step-association">
        <div class="step-header">
            <div class="step-icon orange">
                <span class="material-icons-round">groups</span>
            </div>
            <div>
                <div class="step-title">4 — Association destinataire</div>
                <div class="step-subtitle">Optionnel — notre IA peut sélectionner la meilleure association pour vous</div>
            </div>
        </div>

        {{-- Search --}}
        <div class="assoc-search-box">
            <span class="material-icons-round">search</span>
            <input type="text" class="assoc-search-input" id="assoc-search"
                   placeholder="Rechercher une association par nom ou ville…"
                   onkeyup="filterAssociations(this.value)">
        </div>

        {{-- List --}}
        <div class="assoc-list" id="assoc-list">
            {{-- "No preference / AI Choose" option --}}
            <label class="assoc-card selected" id="assoc-card-none" onclick="selectAssoc(this, '')">
                <input type="radio" name="preferred_association_id" value="" checked>
                <div class="assoc-card-check">
                    <span class="material-icons-round" style="font-size:.75rem;color:white">check</span>
                </div>
                <div class="assoc-card-name" style="display:flex;align-items:center;gap:.35rem">
                    <span class="material-icons-round" style="font-size:1rem;color:var(--primary-light)">auto_awesome</span>
                    Laisser l'IA choisir
                </div>
                <div class="assoc-card-city">Meilleure association selon votre don</div>
            </label>

            @foreach($associations as $assoc)
                <label class="assoc-card" id="assoc-card-{{ $assoc->id }}"
                       onclick="selectAssoc(this, '{{ $assoc->id }}')">
                    <input type="radio" name="preferred_association_id" value="{{ $assoc->id }}"
                           @checked(old('preferred_association_id') == $assoc->id)>
                    <div class="assoc-card-check">
                        <span class="material-icons-round" style="font-size:.75rem;color:white">check</span>
                    </div>
                    <div class="assoc-card-name">{{ $assoc->nom }}</div>
                    @if($assoc->city || $assoc->adresse)
                        <div class="assoc-card-city">
                            <span class="material-icons-round" style="font-size:.75rem">location_on</span>
                            {{ $assoc->city ?? $assoc->adresse }}
                        </div>
                    @endif
                    @if($assoc->isVerified())
                        <div style="margin-top:.4rem">
                            <span class="badge badge-success" style="font-size:.65rem;padding:.1rem .4rem">
                                <span class="material-icons-round" style="font-size:.7rem">verified</span> Vérifiée
                            </span>
                        </div>
                    @endif
                </label>
            @endforeach
        </div>

        {{-- In-page Add new association trigger (stays on page) --}}
        <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem">
            <span style="font-size:.82rem;color:var(--text-muted)">Vous ne trouvez pas l'association souhaitée ?</span>
            <button type="button"
                    onclick="openAddAssocModal()"
                    id="btn-open-add-assoc"
                    style="font-size:.82rem;color:var(--primary-light);background:rgba(108,99,255,.12);border:1px solid rgba(108,99,255,.28);border-radius:var(--radius-sm);padding:.45rem .95rem;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;font-weight:600;transition:var(--transition)"
                    onmouseover="this.style.background='rgba(108,99,255,.22)';this.style.borderColor='var(--primary)'"
                    onmouseout="this.style.background='rgba(108,99,255,.12)';this.style.borderColor='rgba(108,99,255,.28)'">
                <span class="material-icons-round" style="font-size:1rem">add_circle_outline</span>
                Ajouter une nouvelle association
            </button>
        </div>
    </div>

    {{-- ── Actions ────────────────────────────────────────────────────────── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;padding:.5rem 0">
        <a href="{{ route('dons.index') }}" class="btn btn-secondary">
            <span class="material-icons-round">close</span> Annuler
        </a>
        <button type="submit" class="btn btn-primary" style="padding:.75rem 2rem;font-size:.95rem">
            <span class="material-icons-round">psychology</span>
            Soumettre mon don
        </button>
    </div>

    </form>
</div>

{{-- ── Modal : Ajouter une nouvelle association (In-Page, sans quitter) ───── --}}
<div class="modal-overlay" id="add-assoc-modal" onclick="handleModalOverlayClick(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:.6rem">
                <div style="width:36px;height:36px;border-radius:8px;background:rgba(108,99,255,.15);color:var(--primary-light);display:flex;align-items:center;justify-content:center">
                    <span class="material-icons-round" style="font-size:1.2rem">add_business</span>
                </div>
                <div>
                    <h3 style="margin:0;font-size:1.05rem;font-weight:700;color:var(--text-primary)">Ajouter une association</h3>
                    <p style="margin:0;font-size:.75rem;color:var(--text-muted)">Elle sera ajoutée à l'annuaire et sélectionnée pour ce don</p>
                </div>
            </div>
            <button type="button" onclick="closeAddAssocModal()"
                    style="background:none;border:none;color:var(--text-muted);cursor:pointer;padding:.25rem;display:flex;align-items:center;justify-content:center;border-radius:4px"
                    onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-muted)'">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        <form id="form-quick-add-assoc" onsubmit="submitNewAssociation(event)">
            <div class="modal-body" style="display:grid;gap:1rem">
                {{-- Error container inside modal --}}
                <div id="modal-assoc-error" style="display:none;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:var(--accent-red);padding:.75rem;border-radius:var(--radius-sm);font-size:.82rem">
                    <span class="material-icons-round" style="font-size:.95rem;vertical-align:middle;margin-right:.25rem">error</span>
                    <span id="modal-assoc-error-text"></span>
                </div>

                <div>
                    <label class="form-label" for="modal_nom">Nom de l'association <span style="color:var(--accent-red)">*</span></label>
                    <input type="text" id="modal_nom" name="nom" required class="form-control"
                           placeholder="Ex. Croissant Rouge Tunisien">
                </div>

                <div class="fields-grid">
                    <div>
                        <label class="form-label" for="modal_city">Ville</label>
                        <input type="text" id="modal_city" name="city" class="form-control"
                               placeholder="Ex. Tunis, Sfax, Sousse…">
                    </div>
                    <div>
                        <label class="form-label" for="modal_phone">Téléphone</label>
                        <input type="text" id="modal_phone" name="phone" class="form-control"
                               placeholder="+216 71 000 000">
                    </div>
                </div>

                <div>
                    <label class="form-label" for="modal_adresse">Adresse complète</label>
                    <input type="text" id="modal_adresse" name="adresse" class="form-control"
                           placeholder="Rue, numéro, quartier…">
                </div>

                <div class="fields-grid">
                    <div>
                        <label class="form-label" for="modal_capacity">Capacité (kg)</label>
                        <input type="number" id="modal_capacity" name="capacity" class="form-control"
                               placeholder="500" min="1" max="10000" value="250">
                    </div>
                    <div>
                        <label class="form-label" for="modal_beneficiaires">Bénéficiaires aidés</label>
                        <input type="number" id="modal_beneficiaires" name="beneficiaires_aides" class="form-control"
                               placeholder="100" min="0" value="0">
                    </div>
                </div>

                <div>
                    <label class="form-label" for="modal_description">Description / Mission</label>
                    <textarea id="modal_description" name="description" rows="2" class="form-control"
                              placeholder="Mission de l'association, publics ciblés, types de vêtements acceptés…"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeAddAssocModal()" class="btn btn-secondary" style="padding:.5rem 1rem">
                    Annuler
                </button>
                <button type="submit" id="btn-submit-assoc" class="btn btn-primary" style="padding:.5rem 1.25rem">
                    <span class="material-icons-round" style="font-size:1.05rem">check</span>
                    Enregistrer et sélectionner
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Toast Popup Notification ────────────────────────────────────────── --}}
<div class="toast-popup" id="toast-notif">
    <span class="material-icons-round" style="color:var(--secondary);font-size:1.25rem">check_circle</span>
    <span id="toast-message">Action effectuée avec succès</span>
</div>

@endsection

@section('scripts')
<script>
// ── Photo preview ─────────────────────────────────────────────────────────────
function handleFiles(files) {
    const preview = document.getElementById('photo-preview');
    preview.innerHTML = '';
    Array.from(files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const wrap = document.createElement('div');
            wrap.style.cssText = 'position:relative;width:80px;height:80px;border-radius:8px;overflow:hidden;border:1px solid rgba(255,255,255,.1)';
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'width:100%;height:100%;object-fit:cover';
            wrap.appendChild(img);
            preview.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });
}

// Drag-over visual
const dz = document.getElementById('dropzone');
if (dz) {
    dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('drag-over'); });
    dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
    dz.addEventListener('drop', e => { e.preventDefault(); dz.classList.remove('drag-over'); });
}

// ── Association selector ──────────────────────────────────────────────────────
function selectAssoc(card, id) {
    document.querySelectorAll('.assoc-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    const radio = card.querySelector('input[type="radio"]');
    if (radio) { radio.checked = true; }
}

function filterAssociations(q) {
    const lc = q.toLowerCase();
    document.querySelectorAll('.assoc-card').forEach(card => {
        const name = (card.querySelector('.assoc-card-name')?.textContent || '').toLowerCase();
        const city = (card.querySelector('.assoc-card-city')?.textContent || '').toLowerCase();
        card.style.display = (name.includes(lc) || city.includes(lc)) ? '' : 'none';
    });
}

// Restore selected on page load (old input)
const oldAssoc = '{{ old('preferred_association_id', '') }}';
if (oldAssoc) {
    const card = document.getElementById('assoc-card-' + oldAssoc);
    if (card) { selectAssoc(card, oldAssoc); }
}

// ── Geolocation ───────────────────────────────────────────────────────────────
document.getElementById('use-location')?.addEventListener('click', function () {
    if (!navigator.geolocation) return;
    this.innerHTML = '<span class="material-icons-round" style="font-size:.9rem">hourglass_empty</span> Localisation…';
    navigator.geolocation.getCurrentPosition(
        pos => {
            document.getElementById('lat').value = pos.coords.latitude.toFixed(6);
            document.getElementById('lng').value = pos.coords.longitude.toFixed(6);
            this.innerHTML = '<span class="material-icons-round" style="font-size:.9rem">check_circle</span> Position enregistrée ✓';
            this.style.color = 'var(--secondary)';
        },
        () => {
            this.innerHTML = '<span class="material-icons-round" style="font-size:.9rem">error</span> Position indisponible';
            this.style.color = 'var(--accent-red)';
        }
    );
});

// ── Modal : In-Page Association Creation ──────────────────────────────────────
function openAddAssocModal() {
    const modal = document.getElementById('add-assoc-modal');
    modal.classList.add('active');
    document.getElementById('modal-assoc-error').style.display = 'none';
    setTimeout(() => document.getElementById('modal_nom')?.focus(), 100);
}

function closeAddAssocModal() {
    const modal = document.getElementById('add-assoc-modal');
    modal.classList.remove('active');
}

function handleModalOverlayClick(e) {
    if (e.target.id === 'add-assoc-modal') {
        closeAddAssocModal();
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddAssocModal();
    }
});

// Toast notification helper
function showToast(msg) {
    const toast = document.getElementById('toast-notif');
    document.getElementById('toast-message').textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4000);
}

// Submit new association via AJAX without leaving the page
async function submitNewAssociation(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-assoc');
    const errBox = document.getElementById('modal-assoc-error');
    const errText = document.getElementById('modal-assoc-error-text');

    const nom = document.getElementById('modal_nom').value.trim();
    if (!nom) {
        errText.textContent = "Le nom de l'association est obligatoire.";
        errBox.style.display = 'block';
        return;
    }

    const payload = {
        nom: nom,
        city: document.getElementById('modal_city').value.trim() || null,
        phone: document.getElementById('modal_phone').value.trim() || null,
        adresse: document.getElementById('modal_adresse').value.trim() || null,
        capacity: parseInt(document.getElementById('modal_capacity').value) || 250,
        beneficiaires_aides: parseInt(document.getElementById('modal_beneficiaires').value) || 0,
        description: document.getElementById('modal_description').value.trim() || null,
    };

    btn.disabled = true;
    btn.innerHTML = '<span class="material-icons-round" style="font-size:1rem;animation:spin 1s linear infinite">sync</span> Enregistrement…';

    try {
        const response = await fetch('{{ route('associations.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok) {
            let msg = data.message || "Une erreur est survenue lors de l'ajout.";
            if (data.errors) {
                msg = Object.values(data.errors).flat().join(' ');
            }
            errText.textContent = msg;
            errBox.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<span class="material-icons-round" style="font-size:1.05rem">check</span> Enregistrer et sélectionner';
            return;
        }

        // Association successfully created!
        const newAssoc = data.association;

        // Build new card element
        const newCard = document.createElement('label');
        newCard.className = 'assoc-card selected';
        newCard.id = 'assoc-card-' + newAssoc.id;
        newCard.onclick = function() { selectAssoc(this, newAssoc.id); };

        let locationHtml = '';
        if (newAssoc.city || newAssoc.adresse) {
            locationHtml = `<div class="assoc-card-city">
                <span class="material-icons-round" style="font-size:.75rem">location_on</span>
                ${newAssoc.city || newAssoc.adresse}
            </div>`;
        }

        newCard.innerHTML = `
            <input type="radio" name="preferred_association_id" value="${newAssoc.id}" checked>
            <div class="assoc-card-check">
                <span class="material-icons-round" style="font-size:.75rem;color:white">check</span>
            </div>
            <div class="assoc-card-name">${escapeHtml(newAssoc.nom)}</div>
            ${locationHtml}
            <div style="margin-top:.4rem">
                <span class="badge" style="font-size:.65rem;padding:.1rem .4rem;background:rgba(108,99,255,.2);color:var(--primary-light)">
                    <span class="material-icons-round" style="font-size:.7rem">fiber_new</span> Nouvelle
                </span>
            </div>
        `;

        // Unselect other cards
        document.querySelectorAll('.assoc-card').forEach(c => c.classList.remove('selected'));

        // Insert new card right after the "Laisser l'IA choisir" option
        const noneOption = document.getElementById('assoc-card-none');
        if (noneOption && noneOption.nextSibling) {
            noneOption.parentNode.insertBefore(newCard, noneOption.nextSibling);
        } else {
            document.getElementById('assoc-list').appendChild(newCard);
        }

        // Close modal & reset form
        closeAddAssocModal();
        document.getElementById('form-quick-add-assoc').reset();

        // Show feedback toast & scroll to association step
        showToast(`✨ Association "${newAssoc.nom}" créée et sélectionnée !`);
        newCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    } catch (err) {
        errText.textContent = "Erreur de connexion. Veuillez réessayer.";
        errBox.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-icons-round" style="font-size:1.05rem">check</span> Enregistrer et sélectionner';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}
</script>
<style>
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
@endsection
