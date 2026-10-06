{{-- Formulaire partagé create / edit. $transformation est null en création. --}}
@php($t = $transformation ?? null)

<form method="POST" action="{{ $action }}" id="upcyclingForm">
    @csrf
    @if($t) @method('PUT') @endif

    <input type="hidden" name="genere_par_ia" id="genere_par_ia" value="{{ old('genere_par_ia', $t?->genere_par_ia ? 1 : 0) }}">

    {{-- ===== Panneau IA ===== --}}
    <div class="card" style="margin-bottom:1.25rem; border-color:rgba(108,99,255,.35)">
        <div class="card-header">
            <div class="card-title" style="display:flex; align-items:center; gap:.5rem">
                <span class="material-icons-round" style="color:var(--primary-light)">auto_awesome</span>
                Idées d'upcycling par IA
            </div>
            <span class="ai-badge">IA</span>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label" for="depot_id">Vêtement déposé</label>
                <select name="depot_id" id="depot_id" class="form-control @error('depot_id') is-invalid @enderror">
                    <option value="">— Aucun / autre vêtement —</option>
                    @foreach($depots as $depot)
                        <option value="{{ $depot->id }}" @selected(old('depot_id', $t?->depot_id) == $depot->id)>
                            {{ $depot->categorie }} ({{ $depot->etat }}) – #{{ $depot->id }}
                        </option>
                    @endforeach
                </select>
                @error('depot_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="garment">Ou décrivez le vêtement</label>
                <input type="text" id="garment" class="form-control" placeholder="Ex. vieux jean bleu délavé" maxlength="150">
            </div>
        </div>

        <button type="button" id="btnIdeas" class="btn btn-primary" onclick="generateIdeas()">
            <span class="material-icons-round">psychology</span> Générer des idées
        </button>

        <div id="ideasError" class="alert alert-danger" style="display:none; margin-top:1rem"></div>
        <div id="ideasSource" style="display:none; margin-top:1rem; font-size:.8rem; color:var(--text-muted)"></div>
        <div id="ideasBox" class="grid-3" style="margin-top:.75rem"></div>
    </div>

    {{-- ===== Détails du projet ===== --}}
    <div class="card">
        <div class="card-header"><div class="card-title">Détails du projet</div></div>

        <div class="form-group">
            <label class="form-label" for="titre">Titre *</label>
            <input type="text" name="titre" id="titre" value="{{ old('titre', $t?->titre) }}"
                   class="form-control @error('titre') is-invalid @enderror" placeholder="Ex. Sac tote en jean">
            @error('titre')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label class="form-label" for="type_projet">Type de projet *</label>
                <select name="type_projet" id="type_projet" class="form-control @error('type_projet') is-invalid @enderror">
                    <option value="">— Choisir —</option>
                    @foreach(\App\Models\Transformation::TYPES as $type)
                        <option value="{{ $type }}" @selected(old('type_projet', $t?->type_projet) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                @error('type_projet')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="difficulte">Difficulté</label>
                <select name="difficulte" id="difficulte" class="form-control @error('difficulte') is-invalid @enderror">
                    <option value="">— Non précisée —</option>
                    @foreach(\App\Models\Transformation::DIFFICULTES as $key => $label)
                        <option value="{{ $key }}" @selected(old('difficulte', $t?->difficulte) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('difficulte')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="statut">Statut *</label>
                <select name="statut" id="statut" class="form-control @error('statut') is-invalid @enderror">
                    @foreach(\App\Models\Transformation::STATUTS as $key => $label)
                        <option value="{{ $key }}" @selected(old('statut', $t?->statut ?? 'idee') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('statut')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Description</label>
            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                      placeholder="Décrivez les étapes ou l'idée du projet">{{ old('description', $t?->description) }}</textarea>
            @error('description')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label" for="duree_estimee">Durée estimée</label>
                <input type="text" name="duree_estimee" id="duree_estimee" value="{{ old('duree_estimee', $t?->duree_estimee) }}"
                       class="form-control @error('duree_estimee') is-invalid @enderror" placeholder="Ex. 2 h">
                @error('duree_estimee')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="materiaux">Matériaux (séparés par des virgules)</label>
                <input type="text" name="materiaux" id="materiaux"
                       value="{{ old('materiaux', is_array($t?->materiaux) ? implode(', ', $t->materiaux) : '') }}"
                       class="form-control @error('materiaux') is-invalid @enderror" placeholder="Fil, ciseaux, fermeture éclair">
                @error('materiaux')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="display:flex; gap:.75rem; margin-top:.5rem">
            <button type="submit" class="btn btn-primary">
                <span class="material-icons-round">save</span> {{ $t ? 'Enregistrer les modifications' : 'Créer le projet' }}
            </button>
            <a href="{{ $t ? route('transformations.show', $t) : route('transformations.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
    </div>
</form>

@push('scripts')
<script>
let currentIdeas = [];

function setIdeasError(msg) {
    const el = document.getElementById('ideasError');
    el.textContent = msg || '';
    el.style.display = msg ? 'flex' : 'none';
}

async function generateIdeas() {
    const btn = document.getElementById('btnIdeas');
    const depot = document.getElementById('depot_id').value;
    const garment = document.getElementById('garment').value.trim();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    setIdeasError(null);
    if (!depot && !garment) {
        setIdeasError('Choisissez un vêtement déposé ou décrivez-le.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="material-icons-round" style="animation:spin 1s linear infinite">autorenew</span> Génération...';

    try {
        const res = await fetch(@json(route('transformations.suggest')), {
            method: 'POST',
            headers: {'Accept': 'application/json', 'Content-Type': 'application/json', ...(csrf ? {'X-CSRF-TOKEN': csrf} : {})},
            body: JSON.stringify({depot_id: depot || null, garment: garment || null})
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur lors de la génération.');
        currentIdeas = data.ideas || [];
        renderIdeas(data.source);
    } catch (e) {
        setIdeasError(e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-icons-round">psychology</span> Générer des idées';
    }
}

function renderIdeas(source) {
    const box = document.getElementById('ideasBox');
    const src = document.getElementById('ideasSource');
    box.replaceChildren();
    src.style.display = 'block';
    src.textContent = source === 'ia' ? 'Idées générées par l\'IA' : 'Idées du catalogue local (IA non configurée)';

    currentIdeas.forEach((idea, i) => {
        const card = document.createElement('div');
        card.className = 'card';
        card.style.padding = '1rem';

        const h = document.createElement('h3');
        h.style.fontSize = '1rem';
        h.textContent = idea.titre;

        const meta = document.createElement('div');
        meta.style.cssText = 'margin:.5rem 0; display:flex; gap:.4rem; flex-wrap:wrap';
        [idea.type_projet, idea.difficulte, idea.duree_estimee].filter(Boolean).forEach(v => {
            const b = document.createElement('span');
            b.className = 'badge badge-primary';
            b.textContent = v;
            meta.appendChild(b);
        });

        const p = document.createElement('p');
        p.style.cssText = 'font-size:.85rem; color:var(--text-secondary); margin-bottom:.75rem';
        p.textContent = idea.description;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-success btn-sm';
        btn.textContent = 'Utiliser cette idée';
        btn.onclick = () => applyIdea(i);

        card.append(h, meta, p, btn);
        box.appendChild(card);
    });
}

function applyIdea(i) {
    const idea = currentIdeas[i];
    if (!idea) return;
    document.getElementById('titre').value = idea.titre || '';
    document.getElementById('description').value = idea.description || '';
    document.getElementById('duree_estimee').value = idea.duree_estimee || '';
    document.getElementById('materiaux').value = (idea.materiaux || []).join(', ');
    const type = document.getElementById('type_projet');
    if ([...type.options].some(o => o.value === idea.type_projet)) type.value = idea.type_projet;
    const diff = document.getElementById('difficulte');
    if ([...diff.options].some(o => o.value === idea.difficulte)) diff.value = idea.difficulte;
    document.getElementById('genere_par_ia').value = 1;
    document.getElementById('titre').scrollIntoView({behavior: 'smooth', block: 'center'});
}
</script>
@endpush
