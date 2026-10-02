@extends('layouts.app')

@section('title', 'Trouver un atelier de réparation')
@section('meta_description', 'Sélectionnez un atelier partenaire spécialisé pour réparer votre vêtement')
@section('breadcrumb', 'Gestion › Réparations › Choix de l\'Atelier')

@php
    $minCost = $repair->estimated_cost_min ?? 0;
    $maxCost = $repair->estimated_cost_max ?? 0;
@endphp

@push('styles')
<style>
/* ── Modals ── */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.75);
    display: flex; align-items: center; justify-content: center;
    z-index: 9000;
    backdrop-filter: blur(4px);
    opacity: 0; pointer-events: none;
    transition: opacity .2s ease;
}
.modal-overlay.open { opacity: 1; pointer-events: all; }
.modal-box {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 28px;
    width: 94%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
    transform: translateY(14px) scale(.97);
    transition: transform .2s ease;
    box-shadow: 0 20px 60px rgba(0,0,0,.6), 0 0 40px rgba(108,99,255,.12);
}
.modal-overlay.open .modal-box { transform: translateY(0) scale(1); }
.modal-hdr { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.modal-hdr h2 { margin:0; font-size:1.2rem; font-family:'Outfit',sans-serif; }
.btn-close {
    background: none; border: none; color: var(--text-muted);
    font-size: 22px; cursor: pointer; width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 8px; transition: background .15s;
}
.btn-close:hover { background: rgba(255,255,255,.08); color: var(--text-primary); }
.field-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.field-group label { font-size: .82rem; font-weight: 600; color: var(--text-secondary); }
.field-group input {
    padding: .6rem .85rem;
    border: 1px solid var(--border);
    border-radius: 9px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: .9rem;
    font-family: inherit;
    transition: border-color .15s;
}
.field-group input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(108,99,255,.15); }
.field-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.chips-wrap { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 4px; }
.chip-btn {
    padding: .35rem .75rem; border-radius: 999px;
    border: 1px solid var(--border); background: var(--bg-card);
    color: var(--text-secondary); font-size: .78rem; cursor: pointer;
    transition: all .15s;
}
.chip-btn.active {
    background: rgba(108,99,255,.2);
    border-color: var(--primary);
    color: var(--primary-light);
}
.modal-error {
    padding: .65rem .9rem; border-radius: 9px; font-size: .84rem;
    background: rgba(255,101,132,.12); color: var(--accent-red);
    border: 1px solid rgba(255,101,132,.3); margin-bottom: 14px;
}
.modal-footer { display: flex; gap: 10px; margin-top: 22px; }
.modal-footer .btn { flex: 1; justify-content: center; }

/* ── Workshop Card ── */
.wshop-card {
    display: flex; justify-content: space-between; align-items: flex-start;
    flex-wrap: wrap; gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-radius: 14px;
    border: 1px solid var(--border);
    background: var(--bg-card);
    transition: var(--transition);
}
.wshop-card:hover { border-color: var(--border-active); background: var(--bg-card-hover); }
.wshop-card.assigned {
    border-color: var(--secondary) !important;
    background: rgba(67,217,173,.04);
    box-shadow: 0 0 20px rgba(67,217,173,.08);
}
.action-row { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .75rem; }

/* ── Confirm delete ── */
.confirm-box { text-align: center; }
.confirm-box .icon { font-size: 3.5rem; margin-bottom: 12px; }
.confirm-box h3 { font-size: 1.2rem; margin-bottom: 6px; }
.confirm-box p { color: var(--text-secondary); font-size: .9rem; }

/* ── Detail view ── */
.detail-row { display: flex; justify-content: space-between; align-items: flex-start; padding: .65rem 0; border-bottom: 1px solid var(--border); font-size: .9rem; }
.detail-row:last-child { border-bottom: none; }
.detail-label { color: var(--text-muted); }
.detail-val { font-weight: 600; text-align: right; max-width: 260px; }

/* ── Toast ── */
#wToast {
    position: fixed; bottom: 28px; right: 28px; z-index: 99999;
    padding: .8rem 1.3rem; border-radius: 10px;
    font-size: .88rem; font-weight: 600;
    display: flex; align-items: center; gap: .5rem;
    box-shadow: 0 8px 24px rgba(0,0,0,.5);
    transform: translateY(80px); opacity: 0;
    transition: all .3s ease;
    pointer-events: none;
}
#wToast.show { transform: translateY(0); opacity: 1; }
#wToast.success { background: rgba(67,217,173,.15); border: 1px solid rgba(67,217,173,.4); color: var(--secondary); }
#wToast.error   { background: rgba(255,101,132,.15); border: 1px solid rgba(255,101,132,.4); color: var(--accent-red); }
</style>
@endpush

@section('content')
<div class="animate-fade-in-up">

    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem">
        <div>
            <div style="display:flex; align-items:center; gap:.6rem; margin-bottom:.4rem">
                <a href="{{ route('reparations.show', $repair->id) }}" class="btn btn-secondary btn-sm">
                    <span class="material-icons-round">arrow_back</span> Retour rapport
                </a>
                <span class="badge badge-primary">Demande #{{ $repair->id }} — {{ $repair->defect_type ?? 'Défaut textile' }}</span>
            </div>
            <h1 class="page-title" style="margin-top:.35rem">Ateliers & Retoucheurs Partenaires</h1>
            <p class="page-subtitle">Sélectionnez, modifiez ou supprimez les ateliers disponibles pour cette intervention.</p>
        </div>
        <button type="button" onclick="openWorkshopModal()" class="btn btn-primary">
            <span class="material-icons-round">add_business</span> Ajouter un atelier
        </button>
    </div>

    <!-- Résumé de la demande -->
    <div class="card" style="margin-bottom:1.5rem; padding:1.1rem 1.4rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.75rem; border-color:var(--border-active)">
        <div style="display:flex; align-items:center; gap:1rem">
            @if($repair->photo_url)
                <img src="{{ $repair->photo_url }}" alt="Vêtement" style="width:48px; height:48px; object-fit:cover; border-radius:8px; border:1px solid var(--border)">
            @endif
            <div>
                <strong>{{ $repair->defect_type ?? 'Intervention textile' }}</strong>
                <div style="font-size:.82rem; color:var(--text-secondary)">
                    Estimation : <strong style="color:var(--secondary)">{{ $minCost }} DT – {{ $maxCost }} DT</strong>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" onclick="locateUser()">
            <span class="material-icons-round">my_location</span> Trier par proximité
        </button>
    </div>

    <!-- Grille Filtres + Ateliers -->
    <div style="display:grid; grid-template-columns:240px 1fr; gap:1.5rem; align-items:start">

        <!-- Filtres spécialités -->
        <div class="card" style="padding:1.25rem">
            <h3 style="font-size:.92rem; margin-bottom:1rem; display:flex; align-items:center; gap:.4rem">
                <span class="material-icons-round" style="font-size:1.1rem">filter_list</span> Spécialités
            </h3>
            <div id="filterList" style="display:flex; flex-direction:column; gap:.55rem"></div>
        </div>

        <!-- Liste ateliers -->
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.85rem">
                <span id="workshopCount" style="font-size:.85rem; color:var(--text-secondary); font-weight:600">Chargement…</span>
            </div>
            <div id="workshopCards" style="display:flex; flex-direction:column; gap:.9rem"></div>
        </div>

    </div>
</div>

<!-- ════════════════════════════════════════════════════════
     MODAL VOIR (détails lecture seule)
═══════════════════════════════════════════════════════════ -->
<div id="modalVoir" class="modal-overlay" onclick="if(event.target===this)closeModal('modalVoir')">
    <div class="modal-box">
        <div class="modal-hdr">
            <h2 style="display:flex;align-items:center;gap:.5rem">
                <span class="material-icons-round" style="color:var(--primary-light)">storefront</span>
                <span id="voirName">Atelier</span>
            </h2>
            <button class="btn-close" onclick="closeModal('modalVoir')">×</button>
        </div>
        <div id="voirContent"></div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modalVoir')">
                <span class="material-icons-round">close</span> Fermer
            </button>
            <button class="btn btn-primary" id="voirBtnConfier">
                <span class="material-icons-round">handshake</span> Confier à cet atelier
            </button>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════
     MODAL MODIFIER
═══════════════════════════════════════════════════════════ -->
<div id="modalEdit" class="modal-overlay" onclick="if(event.target===this)closeModal('modalEdit')">
    <div class="modal-box">
        <div class="modal-hdr">
            <h2 style="display:flex;align-items:center;gap:.5rem">
                <span class="material-icons-round" style="color:var(--accent-orange)">edit</span>
                Modifier l'atelier
            </h2>
            <button class="btn-close" onclick="closeModal('modalEdit')">×</button>
        </div>
        <div id="editError" class="modal-error" style="display:none"></div>
        <input type="hidden" id="editId">
        <div class="field-group">
            <label>Nom de l'atelier *</label>
            <input type="text" id="editName" placeholder="Atelier Couture Circulaire">
        </div>
        <div class="field-group">
            <label>Adresse *</label>
            <input type="text" id="editAddress" placeholder="12 rue des Artisans">
        </div>
        <div class="field-grid-2">
            <div class="field-group">
                <label>Ville</label>
                <input type="text" id="editCity" placeholder="Paris">
            </div>
            <div class="field-group">
                <label>Téléphone</label>
                <input type="text" id="editPhone" placeholder="+33 1 23 45 67 89">
            </div>
        </div>
        <div class="field-group">
            <label>Spécialités</label>
            <div class="chips-wrap" id="editChips"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modalEdit')">
                <span class="material-icons-round">close</span> Annuler
            </button>
            <button class="btn btn-primary" id="btnSaveEdit" onclick="saveEdit()">
                <span class="material-icons-round">save</span> Enregistrer
            </button>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════
     MODAL SUPPRIMER (confirmation)
═══════════════════════════════════════════════════════════ -->
<div id="modalDelete" class="modal-overlay" onclick="if(event.target===this)closeModal('modalDelete')">
    <div class="modal-box" style="max-width:420px">
        <div class="modal-hdr">
            <h2 style="display:flex;align-items:center;gap:.5rem; color:var(--accent-red)">
                <span class="material-icons-round">delete_forever</span> Supprimer l'atelier
            </h2>
            <button class="btn-close" onclick="closeModal('modalDelete')">×</button>
        </div>
        <div class="confirm-box">
            <div class="icon">🗑️</div>
            <h3>Êtes-vous sûr ?</h3>
            <p>L'atelier <strong id="deleteName"></strong> sera définitivement supprimé de la plateforme. Cette action est irréversible.</p>
        </div>
        <input type="hidden" id="deleteId">
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modalDelete')">
                <span class="material-icons-round">undo</span> Annuler
            </button>
            <button class="btn btn-danger" id="btnConfirmDelete" onclick="confirmDelete()" style="background:rgba(255,101,132,.2); color:var(--accent-red); border:1px solid rgba(255,101,132,.4)">
                <span class="material-icons-round">delete_forever</span> Oui, supprimer
            </button>
        </div>
    </div>
</div>

<!-- Toast notification -->
<div id="wToast">
    <span class="material-icons-round" id="wToastIcon">check_circle</span>
    <span id="wToastMsg"></span>
</div>

@endsection

@push('scripts')
<script>
const REPAIR_ID    = {{ $repair->id }};
const CURRENT_DEF  = @json($repair->defect_type ?? '');
let   allWorkshops  = [];
let   userLat = null, userLng = null;
let   activeSpecs   = [];
let   editSpecs     = [];

const ALL_SPECS = ['Général', 'Trou', 'Déchirure', 'Tache', 'Fermeture cassée',
                   'Bouton manquant', 'Usure', 'Couture', 'Cuir', 'Nettoyage spécialisé'];

/* ── helpers ── */
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function csrf() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }
function toast(msg, type = 'success') {
    const t = document.getElementById('wToast');
    const i = document.getElementById('wToastIcon');
    const m = document.getElementById('wToastMsg');
    t.className = `show ${type}`;
    i.textContent = type === 'success' ? 'check_circle' : 'error';
    m.textContent = msg;
    setTimeout(() => t.className = type, 3200);
}

/* ── Filtres ── */
function renderFilters() {
    const el = document.getElementById('filterList');
    if (!el) return;
    if (CURRENT_DEF && ALL_SPECS.includes(CURRENT_DEF)) activeSpecs = [CURRENT_DEF];
    el.innerHTML = ALL_SPECS.map(s => `
        <label style="display:flex;align-items:center;gap:.55rem;font-size:.85rem;color:var(--text-secondary);cursor:pointer">
            <input type="checkbox" value="${s}" onchange="toggleFilter('${s}',this.checked)"
                   ${activeSpecs.includes(s) ? 'checked' : ''} style="accent-color:var(--primary)">
            ${s}
        </label>`).join('');
}
function toggleFilter(s, on) {
    activeSpecs = on ? [...activeSpecs, s] : activeSpecs.filter(x => x !== s);
    renderWorkshops();
}

/* ── Load & render ── */
async function loadWorkshops() {
    let url = `/api/repairs/${REPAIR_ID}/workshops`;
    if (userLat && userLng) url += `?lat=${userLat}&lng=${userLng}`;
    try {
        const r = await fetch(url);
        const j = await r.json();
        allWorkshops = j.data || [];
        renderWorkshops();
    } catch { console.error('Erreur chargement ateliers'); }
}

function renderWorkshops() {
    const list  = document.getElementById('workshopCards');
    const count = document.getElementById('workshopCount');
    if (!list) return;

    let shown = allWorkshops;
    if (activeSpecs.length) {
        shown = shown.filter(w => {
            const sp = Array.isArray(w.specialties) ? w.specialties : [];
            return sp.some(s => activeSpecs.includes(s)) || sp.includes('Général');
        });
    }
    count.textContent = `${shown.length} atelier(s) disponible(s)`;

    if (!shown.length) {
        list.innerHTML = `<div class="card" style="text-align:center;padding:3rem 1rem">
            <span class="material-icons-round" style="font-size:3rem;color:var(--text-muted)">storefront</span>
            <p style="margin-top:.75rem;color:var(--text-secondary)">Aucun atelier ne correspond aux filtres.</p></div>`;
        return;
    }

    const assigned = {{ $repair->workshop_id ?? -1 }};
    list.innerHTML = shown.map(w => {
        const specs   = Array.isArray(w.specialties) ? w.specialties : [];
        const isAssigned = assigned === w.id;
        const dist    = w.distance ? ` · <span style="color:var(--secondary);font-weight:600">à ${parseFloat(w.distance).toFixed(1)} km</span>` : '';

        return `
        <div class="wshop-card ${isAssigned ? 'assigned' : ''}">
            <!-- Info principale -->
            <div style="flex:1;min-width:240px">
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem">
                    <h3 style="font-size:1.05rem;margin:0">${w.name}</h3>
                    ${isAssigned ? '<span class="badge badge-success">✓ Attribué</span>' : ''}
                </div>
                <div style="font-size:.83rem;color:var(--text-secondary);margin-bottom:.45rem">
                    <span class="material-icons-round" style="font-size:.85rem;vertical-align:middle">place</span>
                    ${w.address || ''}${w.city ? ' (' + w.city + ')' : ''} ${dist}
                </div>
                ${w.phone ? `<div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.45rem">
                    <span class="material-icons-round" style="font-size:.8rem;vertical-align:middle">phone</span> ${w.phone}
                </div>` : ''}
                <div style="display:flex;flex-wrap:wrap;gap:.3rem">
                    ${specs.map(s => `<span class="badge badge-primary" style="font-size:.7rem">${s}</span>`).join('')}
                </div>
            </div>

            <!-- Boutons d'action -->
            <div style="display:flex;flex-direction:column;gap:.4rem;align-items:flex-end">
                <!-- Confier (bouton principal) -->
                <button class="btn ${isAssigned ? 'btn-secondary' : 'btn-primary'}" style="min-width:190px;justify-content:center"
                        onclick="chooseWorkshop(${w.id},'${w.name.replace(/'/g,"\\'").replace(/"/g,'\\"')}')">
                    <span class="material-icons-round">${isAssigned ? 'check' : 'handshake'}</span>
                    ${isAssigned ? 'Déjà sélectionné' : 'Confier à cet atelier'}
                </button>

                <!-- Actions secondaires -->
                <div class="action-row">
                    <button class="btn btn-secondary btn-sm" onclick='openVoir(${JSON.stringify(w)})' title="Voir les détails">
                        <span class="material-icons-round" style="font-size:1rem">visibility</span> Voir
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick='openEdit(${JSON.stringify(w)})' title="Modifier l'atelier"
                            style="color:var(--accent-orange);border-color:rgba(255,167,38,.35)">
                        <span class="material-icons-round" style="font-size:1rem">edit</span> Modifier
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick='openDelete(${w.id},"${w.name.replace(/"/g,'\\"')}")'
                            title="Supprimer l'atelier"
                            style="color:var(--accent-red);border-color:rgba(255,101,132,.35)">
                        <span class="material-icons-round" style="font-size:1rem">delete</span> Supprimer
                    </button>
                </div>
            </div>
        </div>`;
    }).join('');
}

/* ─────────────────────────────────────
   MODAL VOIR
───────────────────────────────────── */
function openVoir(w) {
    document.getElementById('voirName').textContent = w.name;
    const specs = Array.isArray(w.specialties) ? w.specialties : [];

    document.getElementById('voirContent').innerHTML = `
        <div>
            <div class="detail-row">
                <span class="detail-label">📍 Adresse</span>
                <span class="detail-val">${w.address || '—'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">🏙️ Ville</span>
                <span class="detail-val">${w.city || '—'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">📞 Téléphone</span>
                <span class="detail-val">${w.phone || '—'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">⭐ Note</span>
                <span class="detail-val">${w.rating ? w.rating + ' / 5' : '—'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">🔧 Spécialités</span>
                <span class="detail-val">${specs.length ? specs.join(', ') : '—'}</span>
            </div>
            ${w.distance ? `<div class="detail-row">
                <span class="detail-label">📏 Distance</span>
                <span class="detail-val" style="color:var(--secondary)">${parseFloat(w.distance).toFixed(2)} km</span>
            </div>` : ''}
        </div>`;

    document.getElementById('voirBtnConfier').onclick = () => {
        closeModal('modalVoir');
        chooseWorkshop(w.id, w.name);
    };
    openModal('modalVoir');
}

/* ─────────────────────────────────────
   MODAL MODIFIER
───────────────────────────────────── */
function openEdit(w) {
    document.getElementById('editId').value      = w.id;
    document.getElementById('editName').value    = w.name;
    document.getElementById('editAddress').value = w.address || '';
    document.getElementById('editCity').value    = w.city || '';
    document.getElementById('editPhone').value   = w.phone || '';
    document.getElementById('editError').style.display = 'none';

    const specs = Array.isArray(w.specialties) ? w.specialties : [];
    editSpecs = [...specs];

    document.getElementById('editChips').innerHTML = ALL_SPECS.map(s => `
        <button type="button" class="chip-btn ${specs.includes(s) ? 'active' : ''}"
                onclick="toggleEditSpec('${s}',this)">${s}</button>`).join('');

    openModal('modalEdit');
}

function toggleEditSpec(s, el) {
    if (editSpecs.includes(s)) {
        editSpecs = editSpecs.filter(x => x !== s);
        el.classList.remove('active');
    } else {
        editSpecs.push(s);
        el.classList.add('active');
    }
}

async function saveEdit() {
    const id      = document.getElementById('editId').value;
    const name    = document.getElementById('editName').value.trim();
    const address = document.getElementById('editAddress').value.trim();
    const errEl   = document.getElementById('editError');
    const btn     = document.getElementById('btnSaveEdit');

    if (!name || !address) {
        errEl.textContent = 'Le nom et l\'adresse sont obligatoires.';
        errEl.style.display = 'block';
        return;
    }

    btn.disabled = true; btn.textContent = 'Enregistrement…';
    errEl.style.display = 'none';

    try {
        const res = await fetch(`/api/workshops/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf()
            },
            body: JSON.stringify({
                name,
                address,
                city: document.getElementById('editCity').value.trim() || null,
                phone: document.getElementById('editPhone').value.trim() || null,
                specialties: editSpecs.length ? editSpecs : ['Général']
            })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur serveur');

        closeModal('modalEdit');
        await loadWorkshops();
        toast('Atelier modifié avec succès ✓');
    } catch (e) {
        errEl.textContent = e.message;
        errEl.style.display = 'block';
    } finally {
        btn.disabled = false; btn.textContent = 'Enregistrer';
    }
}

/* ─────────────────────────────────────
   MODAL SUPPRIMER
───────────────────────────────────── */
function openDelete(id, name) {
    document.getElementById('deleteId').value  = id;
    document.getElementById('deleteName').textContent = name;
    openModal('modalDelete');
}

async function confirmDelete() {
    const id  = document.getElementById('deleteId').value;
    const btn = document.getElementById('btnConfirmDelete');
    btn.disabled = true; btn.textContent = 'Suppression…';

    try {
        const res = await fetch(`/api/workshops/${id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }
        });
        if (!res.ok) throw new Error('Erreur lors de la suppression');
        closeModal('modalDelete');
        await loadWorkshops();
        toast('Atelier supprimé avec succès.', 'success');
    } catch (e) {
        toast(e.message || 'Erreur lors de la suppression.', 'error');
    } finally {
        btn.disabled = false; btn.textContent = 'Oui, supprimer';
    }
}

/* ─────────────────────────────────────
   CONFIER À UN ATELIER
───────────────────────────────────── */
async function chooseWorkshop(workshopId, workshopName) {
    if (!confirm(`Confier la réparation à "${workshopName}" ?`)) return;
    try {
        const res = await fetch(`/api/repairs/${REPAIR_ID}/workshop`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf()
            },
            body: JSON.stringify({ workshop_id: workshopId })
        });
        if (!res.ok) throw new Error("Erreur lors de l'attribution");
        toast(`Réparation confiée à ${workshopName} ✓`);
        setTimeout(() => window.location.href = `/reparations/${REPAIR_ID}`, 1400);
    } catch (e) {
        toast(e.message || "Erreur lors de l'attribution.", 'error');
    }
}

/* ─────────────────────────────────────
   GÉOLOCALISATION
───────────────────────────────────── */
function locateUser() {
    if (!navigator.geolocation) { toast('Géolocalisation non disponible.', 'error'); return; }
    navigator.geolocation.getCurrentPosition(pos => {
        userLat = pos.coords.latitude;
        userLng = pos.coords.longitude;
        loadWorkshops();
        toast('Tri par proximité activé 📍');
    }, () => toast('Impossible d\'obtenir votre position.', 'error'));
}

/* ─────────────────────────────────────
   Callback pour le modal "Ajouter un atelier" (partials/workshop-modal)
───────────────────────────────────── */
window.onWorkshopSaved = async () => {
    await loadWorkshops();
    toast('Nouvel atelier ajouté ✓');
};

/* ─────────────────────────────────────
   INIT
───────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    renderFilters();
    loadWorkshops();
});
</script>
@endpush
