@extends('layouts.app')

@section('title', isset($article) ? 'Modifier l\'article' : 'Publier un article')
@section('meta_description', 'Modifiez votre vêtement à vendre ou échanger sur TexTileCycle')
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
        <a href="{{ route('marketplace.mes-articles') }}" class="btn btn-secondary btn-sm">
            <span class="material-icons-round" style="font-size:1rem">arrow_back</span> Retour
        </a>
        <div>
            <h1 class="page-title">{{ isset($article) ? 'Modifier l\'article' : 'Publier un article' }}</h1>
            <p class="page-subtitle">{{ isset($article) ? 'Mettez à jour les informations' : 'Ajoutez un vêtement à la marketplace circulaire' }}</p>
        </div>
    </div>

    {{-- ═══ MAIN EDIT FORM (PUT only) ═══ --}}
    <form method="POST"
          action="{{ isset($article) ? route('marketplace.update', $article) : route('marketplace.store') }}"
          enctype="multipart/form-data"
          id="article-form">
        @csrf
        @if(isset($article)) @method('PUT') @endif

        <div class="create-grid">

            {{-- ─── FORM LEFT ─── --}}
            <div>
                {{-- Image Upload --}}
                <div class="card section">
                    <div class="card-header">
                        <div class="card-title">
                            <span class="material-icons-round" style="color:var(--primary);font-size:1.1rem">add_photo_alternate</span>
                            Photos du vêtement
                        </div>
                    </div>
                    <div class="upload-zone" id="upload-zone" onclick="document.getElementById('image-input').click()">
                        <span class="material-icons-round">cloud_upload</span>
                        <div style="margin-top:.75rem;font-weight:600;color:var(--text-secondary)">Cliquez ou glissez une photo</div>
                        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.35rem">JPG, PNG, WebP — max 5 MB</div>
                        @if(isset($article) && $article->image_url)
                            <img src="{{ asset('storage/'.$article->image_url) }}" style="max-height:150px;margin-top:1rem;border-radius:var(--radius-md)" alt="aperçu">
                        @endif
                        <div id="image-preview" style="margin-top:1rem"></div>
                    </div>
                    <input type="file" name="image" id="image-input" accept="image/*" style="display:none" onchange="previewImage(this)">
                    @error('image') <div class="form-error"><span class="material-icons-round" style="font-size:.9rem">error</span> {{ $message }}</div> @enderror
                    <p style="font-size:.78rem;color:var(--text-muted);margin-top:.75rem;display:flex;align-items:center;gap:.35rem">
                        <span class="material-icons-round" style="font-size:.9rem">auto_awesome</span>
                        <strong style="color:var(--primary-light)">IA :</strong> L'IA identifiera automatiquement le type, couleur et état du vêtement
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
                                @foreach(['XS','S','M','L','XL','XXL','36','38','40','42','44','46'] as $t)
                                    <option value="{{ $t }}" {{ old('taille', $article->taille ?? '') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="genre">Genre *</label>
                            <select name="genre" id="genre" class="form-control" required>
                                @foreach(['Femme','Homme','Enfant','Unisexe'] as $g)
                                    <option value="{{ $g }}" {{ old('genre', $article->genre ?? '') == $g ? 'selected' : '' }}>{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="etat">État *</label>
                            <select name="etat" id="etat" class="form-control" required oninput="estimatePrice()">
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

                {{-- Actions --}}
                <div class="card">
                    <div style="display:flex;flex-direction:column;gap:.75rem">
                        {{-- ✅ Submit button (inside the main form) --}}
                        <button type="submit" class="btn btn-primary" id="btn-submit" style="width:100%;justify-content:center;padding:.875rem">
                            <span class="material-icons-round">{{ isset($article) ? 'save' : 'publish' }}</span>
                            {{ isset($article) ? 'Enregistrer les modifications' : 'Publier l\'annonce' }}
                        </button>

                        {{-- 🗑️ Delete button (triggers modal, NOT inside this form) --}}
                        @if(isset($article))
                            <button type="button"
                                    class="btn btn-danger"
                                    style="width:100%;justify-content:center"
                                    id="btn-delete"
                                    onclick="openDeleteModal({{ $article->id }}, '{{ addslashes($article->titre) }}')">
                                <span class="material-icons-round">delete</span> Supprimer l'annonce
                            </button>
                        @endif

                        <a href="{{ route('marketplace.mes-articles') }}" class="btn btn-secondary" style="width:100%;justify-content:center">
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
    {{-- ═══ END MAIN FORM ═══ --}}

</div>

{{-- ═══ DELETE MODAL (outside all forms) ═══ --}}
@if(isset($article))
<style>
.del-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(8px);z-index:2000;display:none;align-items:center;justify-content:center;animation:fadeIn .2s ease}
.del-modal-overlay.open{display:flex}
.del-modal-box{background:var(--bg-card);border:1px solid rgba(255,101,132,.4);border-radius:var(--radius-lg);padding:2.5rem 2rem;max-width:440px;width:92%;animation:fadeInUp .25s ease forwards;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,.6)}
.del-modal-icon{width:72px;height:72px;border-radius:50%;background:rgba(255,101,132,.12);border:2px solid rgba(255,101,132,.3);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem}
.del-modal-icon .material-icons-round{font-size:2.25rem;color:var(--accent-red)}
.del-modal-title{font-family:'Outfit',sans-serif;font-size:1.25rem;font-weight:800;color:var(--text-primary);margin-bottom:.5rem}
.del-modal-text{color:var(--text-secondary);font-size:.9rem;line-height:1.6;margin-bottom:1.75rem}
.del-modal-article{display:inline-block;background:rgba(255,101,132,.08);border:1px solid rgba(255,101,132,.2);border-radius:var(--radius-sm);padding:.35rem .875rem;font-size:.85rem;font-weight:600;color:var(--accent-red);margin-bottom:1.25rem}
.del-modal-actions{display:flex;gap:.75rem;justify-content:center}
</style>
<div class="del-modal-overlay" id="del-modal" role="dialog" aria-modal="true">
    <div class="del-modal-box">
        <div class="del-modal-icon">
            <span class="material-icons-round">warning_amber</span>
        </div>
        <div class="del-modal-title">Supprimer cette annonce ?</div>
        <div class="del-modal-article" id="del-modal-name">{{ $article->titre }}</div>
        <div class="del-modal-text">
            Cette action est <strong style="color:var(--accent-red)">irréversible</strong>.<br>
            L'annonce et toutes ses données seront définitivement supprimées.
        </div>
        <div class="del-modal-actions">
            <button type="button" class="btn btn-secondary" id="btn-cancel-del" onclick="closeDeleteModal()" style="min-width:120px">
                <span class="material-icons-round" style="font-size:1rem">close</span> Annuler
            </button>
            {{-- ✅ Standalone delete form — completely outside the edit form --}}
            <form method="POST" action="{{ route('marketplace.destroy', $article) }}" id="del-form" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" id="btn-confirm-del" style="min-width:140px">
                    <span class="material-icons-round" style="font-size:1rem">delete_forever</span> Oui, supprimer
                </button>
            </form>
        </div>
    </div>
</div>
@endif
@endsection


@section('scripts')
<script>
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

function openDeleteModal() {
    const modal = document.getElementById('del-modal');
    if (modal) modal.classList.add('open');
}
function closeDeleteModal() {
    const modal = document.getElementById('del-modal');
    if (modal) modal.classList.remove('open');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeleteModal();
});
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('del-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });
    }
});
</script>
@endsection
