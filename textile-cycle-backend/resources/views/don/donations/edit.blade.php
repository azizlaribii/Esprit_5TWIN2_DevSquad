@extends('layouts.app')

@section('title', 'Modifier le don : ' . $donation->title)
@section('meta_description', 'Modifiez les informations et caractéristiques de votre don textile')
@section('breadcrumb', 'Gestion › Dons › Modifier')

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
    .step-icon.green  { background: rgba(67,217,173,.15);  color: var(--secondary); }
    .step-icon.blue   { background: rgba(41,182,246,.15);  color: var(--accent-blue); }
    .step-icon.purple { background: rgba(108,99,255,.15); color: var(--primary-light); }
    .step-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); }
    .step-subtitle { font-size: .8rem; color: var(--text-muted); margin-top: .1rem; }
    .fields-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .fields-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
    @media(max-width: 700px) {
        .fields-grid, .fields-grid-3 { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
@php
    use App\Support\Textile;
    $isNeedsReview = ($donation->status === \App\Models\Donation::NEEDS_REVIEW);
@endphp

<div class="animate-fade-in-up">

    {{-- Top Back link --}}
    <div style="margin-bottom:1.5rem">
        <a href="{{ route('donations.show', $donation) }}"
           style="color:var(--text-muted);font-size:.85rem;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:var(--transition)"
           onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
            <span class="material-icons-round" style="font-size:1.1rem">arrow_back</span>
            Annuler et revenir aux détails du don
        </a>
        <div style="display:flex;align-items:center;gap:1rem;margin-top:.5rem;flex-wrap:wrap">
            <h1 class="page-title" style="margin-bottom:0">Modifier le don</h1>
            @if($isNeedsReview)
                <span class="badge badge-warning">Action requise</span>
            @endif
        </div>
        <p class="page-subtitle" style="margin-top:.35rem">
            Mettez à jour les caractéristiques de votre vêtement pour recalculer la compatibilité avec les associations.
        </p>
    </div>

    {{-- Alert if NEEDS REVIEW --}}
    @if ($isNeedsReview)
        <div class="alert alert-warning" style="margin-bottom:1.5rem">
            <span class="material-icons-round" style="font-size:1.25rem">assignment_late</span>
            <div>
                <strong>Informations requises :</strong> L'analyse automatique de vos photos n'a pas pu identifier la catégorie et l'état. Veuillez les renseigner pour que nous puissions trouver les associations les plus pertinentes.
            </div>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:1.5rem">
            <span class="material-icons-round">error_outline</span>
            <div>
                <strong>Veuillez corriger les erreurs suivantes :</strong>
                <ul style="margin:.35rem 0 0;padding-left:1.25rem">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Existing Photos Summary --}}
    @if($donation->photos->isNotEmpty())
        <div class="form-step" style="padding:1.25rem 1.75rem">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.875rem">
                <div style="display:flex;align-items:center;gap:.5rem;font-size:.9rem;font-weight:700;color:var(--text-primary)">
                    <span class="material-icons-round" style="color:var(--primary-light);font-size:1.1rem">photo_library</span>
                    Photos actuelles du don
                </div>
                <span style="font-size:.8rem;color:var(--text-muted)">{{ $donation->photos->count() }} photo(s)</span>
            </div>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap">
                @foreach($donation->photos as $photo)
                    <img src="{{ $photo->url() }}" alt="" style="width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
                @endforeach
            </div>
        </div>
    @endif

    <form novalidate method="POST" action="{{ route('donations.update', $donation) }}">
    @csrf
    @method('PUT')

    {{-- ── SECTION 1 : Informations de base ──────────────────────────────── --}}
    <div class="form-step">
        <div class="step-header">
            <div class="step-icon green">
                <span class="material-icons-round">edit_note</span>
            </div>
            <div>
                <div class="step-title">1 — Informations de base</div>
                <div class="step-subtitle">Titre, description, quantité et ville</div>
            </div>
        </div>

        <div style="display:grid;gap:1rem">
            <div>
                <label class="form-label" for="title">Titre du don <span style="color:var(--accent-red)">*</span></label>
                <input id="title" name="title" type="text" required maxlength="120" class="form-control"
                       placeholder="Ex. Manteau d'hiver enfant + 2 pulls laine"
                       value="{{ old('title', $donation->title) }}">
                @include('don.partials.field-error', ['name' => 'title'])
            </div>

            <div>
                <label class="form-label" for="description">Description <span style="color:var(--text-muted);font-weight:400">(facultatif)</span></label>
                <textarea id="description" name="description" rows="3" maxlength="2000" class="form-control"
                          placeholder="Marque, matière, défauts éventuels…">{{ old('description', $donation->description) }}</textarea>
                @include('don.partials.field-error', ['name' => 'description'])
            </div>

            <div class="fields-grid">
                <div>
                    <label class="form-label" for="quantity">Nombre de pièces <span style="color:var(--accent-red)">*</span></label>
                    <input id="quantity" name="quantity" type="number" min="1" max="200" required
                           class="form-control" value="{{ old('quantity', $donation->quantity ?? 1) }}">
                    @include('don.partials.field-error', ['name' => 'quantity'])
                </div>

                <div>
                    <label class="form-label" for="city">Ville <span style="color:var(--accent-red)">*</span></label>
                    <input id="city" name="city" type="text" required maxlength="100" class="form-control"
                           placeholder="Tunis" value="{{ old('city', $donation->city) }}">
                    @include('don.partials.field-error', ['name' => 'city'])
                    <input type="hidden" id="lat" name="lat" value="{{ old('lat', $donation->lat) }}">
                    <input type="hidden" id="lng" name="lng" value="{{ old('lng', $donation->lng) }}">
                    <button type="button" id="use-location"
                            style="margin-top:.4rem;font-size:.75rem;color:var(--primary-light);background:none;border:none;cursor:pointer;padding:0;display:flex;align-items:center;gap:.25rem">
                        <span class="material-icons-round" style="font-size:.9rem">my_location</span>
                        Utiliser ma position GPS pour affiner la recherche
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SECTION 2 : Caractéristiques textiles ────────────────────────── --}}
    <div class="form-step">
        <div class="step-header">
            <div class="step-icon blue">
                <span class="material-icons-round">checkroom</span>
            </div>
            <div>
                <div class="step-title">2 — Caractéristiques textiles</div>
                <div class="step-subtitle">
                    {{ $isNeedsReview ? 'La catégorie et l\'état sont obligatoires pour valider le don' : 'Corrigez les attributs selon vos préférences' }}
                </div>
            </div>
        </div>

        <div class="fields-grid-3">
            @foreach([
                ['category',  'Catégorie',  Textile::CATEGORIES, $isNeedsReview],
                ['condition', 'État',       Textile::CONDITIONS, $isNeedsReview],
                ['age_group', 'Public',     Textile::AGE_GROUPS, false],
                ['gender',    'Genre',      Textile::GENDERS, false],
                ['season',    'Saison',     Textile::SEASONS, false],
            ] as [$field, $label, $options, $isRequired])
            <div>
                <label class="form-label" for="{{ $field }}">
                    {{ $label }}
                    @if($isRequired)<span style="color:var(--accent-red)">*</span>@endif
                </label>
                <select id="{{ $field }}" name="{{ $field }}" class="form-control" @required($isRequired)>
                    <option value="">{{ $isRequired ? '— Choisir obligatoire —' : '— Non précisé —' }}</option>
                    @foreach($options as $key => $optLabel)
                        <option value="{{ $key }}" @selected(old($field, $donation->{$field}) === $key)>{{ $optLabel }}</option>
                    @endforeach
                </select>
                @include('don.partials.field-error', ['name' => $field])
            </div>
            @endforeach

            <div>
                <label class="form-label" for="size">Taille</label>
                <input id="size" name="size" type="text" list="sizes" maxlength="20"
                       class="form-control" placeholder="M, 5-6a, 42…" value="{{ old('size', $donation->size) }}">
                @include('don.partials.field-error', ['name' => 'size'])
                <datalist id="sizes">
                    @foreach(Textile::SIZE_ORDER as $s)<option value="{{ $s }}">@endforeach
                </datalist>
            </div>
        </div>
    </div>

    {{-- ── Form Actions ──────────────────────────────────────────────────── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;padding:.5rem 0">
        <a href="{{ route('donations.show', $donation) }}" class="btn btn-secondary">
            <span class="material-icons-round">close</span> Annuler
        </a>
        <button type="submit" class="btn btn-primary" style="padding:.75rem 2rem;font-size:.95rem">
            <span class="material-icons-round">sync</span>
            Enregistrer et recalculer les correspondances
        </button>
    </div>

    </form>
</div>

@endsection

@section('scripts')
<script>
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
</script>
@endsection
