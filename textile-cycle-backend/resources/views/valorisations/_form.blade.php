<div class="form-group">
    <label class="form-label">Dépôt *</label>
    <select name="depot_id" class="form-control @error('depot_id') is-invalid @enderror">
        <option value="">Sélectionner...</option>
        @foreach($depots as $d)
            <option value="{{ $d->id }}"
                @selected((string) old('depot_id', $valorisation->depot_id ?? $depotId ?? '') === (string) $d->id)>
                #{{ $d->id }} · {{ $d->categorie }} ({{ $d->quantite }} pièces)
            </option>
        @endforeach
    </select>
    @error('depot_id') <div class="form-error">{{ $message }}</div> @enderror
</div>

<div class="grid-2">
    <div class="form-group">
        <label class="form-label">Filière *</label>
        <select name="filiere" class="form-control @error('filiere') is-invalid @enderror">
            @foreach($filieres as $k => $v)
                <option value="{{ $k }}" @selected(old('filiere', $valorisation->filiere ?? '') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        @error('filiere') <div class="form-error">{{ $message }}</div> @enderror
    </div>

    @if(auth()->user()->role === 'admin')
        <div class="form-group">
            <label class="form-label">Statut (administrateur)</label>
            <select name="statut" class="form-control @error('statut') is-invalid @enderror">
                @foreach($statuts as $k => $v)
                    <option value="{{ $k }}" @selected(old('statut', $valorisation->statut ?? 'propose') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            @error('statut') <div class="form-error">{{ $message }}</div> @enderror
        </div>
    @else
        <div class="form-group">
            <label class="form-label">Statut</label>
            <div>
                <span class="badge badge-warning">{{ $statuts[$valorisation->statut ?? 'propose'] }}</span>
            </div>
            <div style="font-size:.75rem;color:var(--text-muted);margin-top:.35rem">
                Le statut est géré par l'administrateur.
            </div>
        </div>
    @endif
</div>

<div class="form-group">
    <label class="form-label">Estimation du prix (DT)</label>
    <input type="number" step="0.01" name="estimation_prix"
           class="form-control @error('estimation_prix') is-invalid @enderror"
           value="{{ old('estimation_prix', $valorisation->estimation_prix ?? '') }}" placeholder="Pour la revente">
    @error('estimation_prix') <div class="form-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label class="form-label">Commentaire</label>
    <textarea name="commentaire" class="form-control @error('commentaire') is-invalid @enderror"
              placeholder="Précisions sur la valorisation...">{{ old('commentaire', $valorisation->commentaire ?? '') }}</textarea>
    @error('commentaire') <div class="form-error">{{ $message }}</div> @enderror
</div>