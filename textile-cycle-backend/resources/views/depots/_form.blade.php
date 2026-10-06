@php
    $etats = ['Très bon état', 'Bon état', 'A réparer', 'Usé'];
    $statuts = ['en_attente' => 'En attente', 'valide' => 'Validé', 'traite' => 'Traité'];
@endphp

<div class="form-group">
    <label class="form-label">Catégorie *</label>
    <input type="text" name="categorie" class="form-control @error('categorie') is-invalid @enderror"
           value="{{ old('categorie', $depot->categorie ?? '') }}" placeholder="Ex: T-Shirts & Tops">
    @error('categorie') <div class="form-error">{{ $message }}</div> @enderror
</div>

<div class="grid-2">
    <div class="form-group">
        <label class="form-label">Quantité *</label>
        <input type="number" name="quantite" class="form-control @error('quantite') is-invalid @enderror"
               value="{{ old('quantite', $depot->quantite ?? 1) }}">
        @error('quantite') <div class="form-error">{{ $message }}</div> @enderror
    </div>

    <div class="form-group">
        <label class="form-label">État *</label>
        <select name="etat" class="form-control @error('etat') is-invalid @enderror">
            @foreach($etats as $e)
                <option value="{{ $e }}" @selected(old('etat', $depot->etat ?? '') === $e)>{{ $e }}</option>
            @endforeach
        </select>
        @error('etat') <div class="form-error">{{ $message }}</div> @enderror
    </div>
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
    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
              placeholder="Décrivez les vêtements déposés...">{{ old('description', $depot->description ?? '') }}</textarea>
    @error('description') <div class="form-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label class="form-label">Photo (JPG, PNG, WebP, max 5 Mo)</label>
    <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
    @error('photo') <div class="form-error">{{ $message }}</div> @enderror
    @isset($depot)
        @if($depot->photo)
            <img src="{{ asset('storage/'.$depot->photo) }}" style="max-width:160px;margin-top:.75rem;border-radius:8px">
        @endif
    @endisset
</div>