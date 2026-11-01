@php($c = $categorie ?? null)

<form method="POST" action="{{ $action }}" class="card">
    @csrf
    @if($c) @method('PUT') @endif

    <div class="form-group">
        <label class="form-label" for="nom">Nom *</label>
        <input type="text" id="nom" name="nom" value="{{ old('nom', $c?->nom) }}" placeholder="Ex. Maroquinerie"
               class="form-control @error('nom') is-invalid @enderror">
        @error('nom')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="description">Description</label>
        <input type="text" id="description" name="description" value="{{ old('description', $c?->description) }}"
               class="form-control @error('description') is-invalid @enderror">
        @error('description')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="icone">Icône (nom Material Icons)</label>
        <input type="text" id="icone" name="icone" value="{{ old('icone', $c?->icone ?? 'category') }}" placeholder="shopping_bag"
               class="form-control @error('icone') is-invalid @enderror">
        @error('icone')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <label style="display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem; color:var(--text-secondary)">
        <input type="checkbox" name="actif" value="1" @checked(old('actif', $c?->actif ?? true))>
        Catégorie active (proposée aux utilisateurs)
    </label>

    <div style="display:flex; gap:.75rem">
        <button type="submit" class="btn btn-primary">
            <span class="material-icons-round">save</span> {{ $c ? 'Enregistrer' : 'Créer la catégorie' }}
        </button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Annuler</a>
    </div>
</form>
