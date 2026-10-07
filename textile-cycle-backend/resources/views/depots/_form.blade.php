@php
    $etats = ['Très bon état', 'Bon état', 'A réparer', 'Usé'];
    $statuts = ['en_attente' => 'En attente', 'valide' => 'Validé', 'traite' => 'Traité'];
@endphp

{{-- Photo + analyse IA --}}
<div class="form-group">
    <label class="form-label">Photo (JPG, PNG, WebP, max 5 Mo)</label>
    <input type="file" id="photo" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
    @error('photo') <div class="form-error">{{ $message }}</div> @enderror

    @isset($depot)
        @if($depot->photo)
            <img src="{{ asset('storage/'.$depot->photo) }}" style="max-width:160px;margin-top:.75rem;border-radius:8px">
        @endif
    @endisset

    <div style="margin-top:.75rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
        <button type="button" id="btn-ia" class="btn btn-secondary btn-sm">
            <span class="material-icons-round" style="font-size:1.1rem">auto_awesome</span>
            Analyser avec l'IA
        </button>
        <span id="ia-status" style="font-size:.85rem;color:var(--text-secondary)"></span>
    </div>

    <div id="ia-result" style="display:none;margin-top:.75rem" class="alert alert-info"></div>
</div>

<input type="hidden" name="ai_type"      id="ai_type"      value="{{ old('ai_type', $depot->ai_type ?? '') }}">
<input type="hidden" name="ai_couleur"   id="ai_couleur"   value="{{ old('ai_couleur', $depot->ai_couleur ?? '') }}">
<input type="hidden" name="ai_etat"      id="ai_etat"      value="{{ old('ai_etat', $depot->ai_etat ?? '') }}">
<input type="hidden" name="ai_matiere"   id="ai_matiere"   value="{{ old('ai_matiere', $depot->ai_matiere ?? '') }}">
<input type="hidden" name="ai_confiance" id="ai_confiance" value="{{ old('ai_confiance', $depot->ai_confiance ?? '') }}">

<div class="grid-2">
    <div class="form-group">
        <label class="form-label">Catégorie *</label>
        <input type="text" id="categorie" name="categorie" class="form-control @error('categorie') is-invalid @enderror"
               value="{{ old('categorie', $depot->categorie ?? '') }}" placeholder="Ex: T-Shirts & Tops">
        @error('categorie') <div class="form-error">{{ $message }}</div> @enderror
    </div>

    <div class="form-group">
        <label class="form-label">Quantité *</label>
        <input type="number" name="quantite" class="form-control @error('quantite') is-invalid @enderror"
               value="{{ old('quantite', $depot->quantite ?? 1) }}">
        @error('quantite') <div class="form-error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="form-group">
    <label class="form-label">État *</label>
    <select id="etat" name="etat" class="form-control @error('etat') is-invalid @enderror">
        @foreach($etats as $e)
            <option value="{{ $e }}" @selected(old('etat', $depot->etat ?? '') === $e)>{{ $e }}</option>
        @endforeach
    </select>
    @error('etat') <div class="form-error">{{ $message }}</div> @enderror
</div>

@isset($depot)
<div class="form-group">
    <label class="form-label">Statut</label>
    <select name="statut" class="form-control">
        @foreach($statuts as $k => $v)
            <option value="{{ $k }}" @selected(old('statut', $depot->statut) === $k)>{{ $v }}</option>
        @endforeach
    </select>
</div>
@endisset

<div class="form-group">
    <label class="form-label">Description</label>
    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
              placeholder="Décrivez les vêtements déposés...">{{ old('description', $depot->description ?? '') }}</textarea>
    @error('description') <div class="form-error">{{ $message }}</div> @enderror
</div>

<script>
document.getElementById('btn-ia').addEventListener('click', async () => {
    const fileInput = document.getElementById('photo');
    const status = document.getElementById('ia-status');
    const box = document.getElementById('ia-result');

    if (!fileInput.files.length) {
        status.textContent = "Choisissez d'abord une photo.";
        return;
    }

    status.textContent = 'Analyse en cours...';
    box.style.display = 'none';

    const fd = new FormData();
    fd.append('photo', fileInput.files[0]);
    fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    try {
        const res = await fetch("{{ route('depots.analyser') }}", {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: fd,
        });
        const data = await res.json();

        if (!res.ok) {
            status.textContent = data.message || "Erreur d'analyse. Remplissez manuellement.";
            return;
        }

        document.getElementById('categorie').value = data.type;
        document.getElementById('etat').value = data.etat;
        const desc = document.getElementById('description');
        if (!desc.value.trim()) {
            desc.value = `${data.type} de couleur ${data.couleur}, matière probable : ${data.matiere}.`;
        }

        document.getElementById('ai_type').value = data.type;
        document.getElementById('ai_couleur').value = data.couleur;
        document.getElementById('ai_etat').value = data.etat;
        document.getElementById('ai_matiere').value = data.matiere;
        document.getElementById('ai_confiance').value = data.confiance;

        status.textContent = '';
        box.innerHTML = `<strong>Résultat IA :</strong> ${data.type} · ${data.couleur} · ${data.matiere} · ${data.etat} (confiance ${data.confiance}%). Vous pouvez corriger les champs avant d'enregistrer.`;
        box.style.display = 'block';
    } catch (e) {
        status.textContent = 'Service IA injoignable. Remplissez manuellement.';
    }
});
</script>