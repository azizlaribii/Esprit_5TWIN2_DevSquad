@extends('layouts.app')

@section('title', 'Trouver un atelier')

@php
    $minCost = $repair->estimated_cost_min ?? 0;
    $maxCost = $repair->estimated_cost_max ?? 0;
    $avgCost = round(($minCost + $maxCost) / 2);
@endphp

@push('styles')
<style>
    .action-btn-group {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
        margin-top: auto;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-soft);
    }
    .btn-act {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.8rem;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.15s ease;
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text);
    }
    .btn-act:hover {
        background: var(--bg-elevated);
        border-color: var(--text-dim);
    }
    .btn-act-detail {
        color: #93c5fd;
        border-color: rgba(59, 130, 246, 0.4);
    }
    .btn-act-detail:hover {
        background: rgba(59, 130, 246, 0.12);
        border-color: #3b82f6;
    }
    .btn-act-edit {
        color: #fcd34d;
        border-color: rgba(245, 158, 11, 0.4);
    }
    .btn-act-edit:hover {
        background: rgba(245, 158, 11, 0.12);
        border-color: #f59e0b;
    }
    .btn-act-delete {
        color: #fca5a5;
        border-color: rgba(239, 68, 68, 0.4);
    }
    .btn-act-delete:hover {
        background: rgba(239, 68, 68, 0.15);
        border-color: #ef4444;
    }
    .btn-danger {
        background: #dc2626;
        color: #ffffff;
        border: none;
        padding: 0.6rem 1rem;
        border-radius: 0.6rem;
        font-weight: 700;
        cursor: pointer;
        transition: filter 0.15s;
    }
    .btn-danger:hover {
        filter: brightness(1.15);
    }
    .toast-msg {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: #15803d;
        color: #fff;
        padding: 0.85rem 1.25rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        z-index: 2000;
        display: none;
        animation: fadeInToast 0.3s ease;
    }
    @keyframes fadeInToast {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .detail-row {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
        align-items: baseline;
    }
    .detail-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        font-family: var(--font-mono);
        color: var(--text-dim);
        min-width: 90px;
    }
    .detail-val {
        font-size: 0.95rem;
        color: var(--text);
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="page-wide">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem">
        <a class="back-link" href="{{ route('reparations.show', $repair->id) }}" style="white-space:nowrap">← Retour</a>
        <button type="button" onclick="openWorkshopModal()"
                style="width:auto; flex:0 0 auto; padding:0.5rem 1rem; font-size:0.85rem;
                       font-weight:700; border:none; border-radius:0.6rem; cursor:pointer;
                       background:#22c55e; color:#fff; white-space:nowrap">
            + Ajouter un atelier
        </button>
    </div>

    <span class="found-count" id="foundCount">Recherche des ateliers...</span>
    <h1 class="hero-title" style="font-size:1.9rem">Trouver un atelier</h1>

    <div id="loadingState">
        <p>Chargement des ateliers...</p>
    </div>

    <div class="workshops-layout" id="workshopsLayout" style="display:none">
        <!-- Filtres -->
        <div class="panel">
            <div class="filters-title">Trier</div>
            <button class="geo-btn" onclick="useMyLocation()" style="width:100%">
                📍 Trier par proximité
            </button>

            <div class="filters-title">Spécialité</div>
            <div id="specialtiesFilterList"></div>

            <div class="filters-title">Note minimum</div>
            <label class="filter-check">
                <input type="radio" name="rating" checked onchange="setMinRating(0)" />
                Toutes
            </label>
            <label class="filter-check">
                <input type="radio" name="rating" onchange="setMinRating(4)" />
                ≥ 4 ★
            </label>
            <label class="filter-check">
                <input type="radio" name="rating" onchange="setMinRating(4.5)" />
                ≥ 4.5 ★
            </label>
        </div>

        <!-- Liste des ateliers -->
        <div>
            <div id="emptyWorkshops" class="empty-state" style="display:none">
                Aucun atelier ne correspond à ces filtres.
            </div>
            <div id="workshopsList"></div>
        </div>
    </div>
</div>

<!-- ================= MODAL DÉTAILS ATELIER ================= -->
<div id="detailWorkshopModal" class="modal-backdrop" style="display:none" onclick="if(event.target===this)closeDetailModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h2 id="detModalTitle">Détails de l'atelier</h2>
            <button type="button" class="modal-close" onclick="closeDetailModal()">&#10005;</button>
        </div>

        <div style="margin: 1.25rem 0;">
            <div class="detail-row">
                <span class="detail-label">Nom</span>
                <span class="detail-val" id="detName" style="font-weight:700; font-size:1.1rem; color:#fff"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Note</span>
                <span class="detail-val" id="detRating" style="color:var(--yellow)"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Adresse</span>
                <span class="detail-val" id="detAddress"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Ville</span>
                <span class="detail-val" id="detCity"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Téléphone</span>
                <span class="detail-val" id="detPhone"></span>
            </div>
            <div class="detail-row" style="align-items:flex-start">
                <span class="detail-label">Spécialités</span>
                <div class="detail-val" id="detSpecialties" style="display:flex; flex-wrap:wrap; gap:0.4rem"></div>
            </div>
        </div>

        <div class="modal-actions" style="margin-top:1.5rem">
            <button type="button" class="btn-outline" onclick="closeDetailModal()">Fermer</button>
            <button type="button" class="btn-act-edit btn-act" id="btnDetToEdit" style="justify-content:center; padding:0.6rem 1rem">✏️ Modifier</button>
            <button type="button" class="btn-choose" id="btnDetChoose" style="padding:0.6rem 1rem">Choisir cet atelier</button>
        </div>
    </div>
</div>

<!-- ================= MODAL MODIFIER ATELIER ================= -->
<div id="editWorkshopModal" class="modal-backdrop" style="display:none" onclick="if(event.target===this)closeEditModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Modifier l'atelier</h2>
            <button type="button" class="modal-close" onclick="closeEditModal()">&#10005;</button>
        </div>
        <div id="editModalError" class="modal-error" style="display:none"></div>

        <input type="hidden" id="editWId" />
        <label class="modal-field">Nom de l'atelier *<input type="text" id="editWName" placeholder="Ex: Atelier Couture"/></label>
        <label class="modal-field">Adresse *<input type="text" id="editWAddress" placeholder="Ex: 12 rue de la Paix"/></label>
        <label class="modal-field">Ville<input type="text" id="editWCity" placeholder="Ex: Paris"/></label>
        <label class="modal-field">Téléphone<input type="text" id="editWPhone" placeholder="Ex: +33 1 23 45 67 89"/></label>
        
        <p class="modal-field" style="margin-bottom:.5rem">Spécialités</p>
        <div class="modal-chips" id="editSpecialtyChips"></div>

        <div class="modal-actions">
            <button type="button" class="btn-outline" onclick="closeEditModal()">Annuler</button>
            <button type="button" class="btn-save" id="btnSaveEditWorkshop" onclick="submitEditWorkshop()">Enregistrer les modifications</button>
        </div>
    </div>
</div>

<!-- ================= MODAL SUPPRIMER ATELIER ================= -->
<div id="deleteWorkshopModal" class="modal-backdrop" style="display:none" onclick="if(event.target===this)closeDeleteModal()">
    <div class="modal-box" style="max-width:440px">
        <div class="modal-header">
            <h2 style="color:#ef4444">Supprimer l'atelier</h2>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">&#10005;</button>
        </div>

        <p style="margin: 1rem 0 1.5rem; color: var(--text-muted); font-size: 0.95rem; line-height: 1.5">
            Êtes-vous sûr de vouloir supprimer définitivement l'atelier <strong id="deleteWName" style="color:#fff"></strong> ?
        </p>

        <div id="deleteModalError" class="modal-error" style="display:none"></div>

        <div class="modal-actions">
            <button type="button" class="btn-outline" onclick="closeDeleteModal()">Annuler</button>
            <button type="button" class="btn-danger" id="btnConfirmDelete" onclick="submitDeleteWorkshop()">🗑️ Supprimer</button>
        </div>
    </div>
</div>

<!-- Toast notification -->
<div id="toastNotification" class="toast-msg"></div>

@endsection

@push('scripts')
<script>
const repairId = {{ $repair->id }};
const averageCost = {{ $avgCost }};
let allWorkshops = [];
let selectedSpecialties = [];
let minRating = 0;
let editSelSpecs = [];
let deletingWorkshopId = null;

function showToast(message, isError = false) {
    const toast = document.getElementById('toastNotification');
    toast.textContent = message;
    toast.style.background = isError ? '#dc2626' : '#15803d';
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3200);
}

async function loadWorkshops(coords) {
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('workshopsLayout').style.display = 'none';

    let url = `${API_BASE}/reparations/${repairId}/ateliers`;
    if (coords) {
        url += `?lat=${coords.lat}&lng=${coords.lng}`;
    }

    try {
        const res = await fetch(url);
        const data = await res.json();
        allWorkshops = data.data || [];
        initSpecialtiesFilter();
        render();
    } catch(err) {
        console.error(err);
    } finally {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('workshopsLayout').style.display = 'grid';
    }
}

function initSpecialtiesFilter() {
    const set = new Set();
    allWorkshops.forEach(w => {
        (w.specialties || []).forEach(s => {
            if (s !== 'Général') set.add(s);
        });
    });

    const container = document.getElementById('specialtiesFilterList');
    container.innerHTML = Array.from(set).map(spec => `
        <label class="filter-check">
            <input type="checkbox" onchange="toggleSpecialty('${spec}', this.checked)" ${selectedSpecialties.includes(spec) ? 'checked' : ''} />
            ${spec}
        </label>
    `).join('');
}

function toggleSpecialty(spec, checked) {
    if (checked) {
        if (!selectedSpecialties.includes(spec)) selectedSpecialties.push(spec);
    } else {
        selectedSpecialties = selectedSpecialties.filter(s => s !== spec);
    }
    render();
}

function setMinRating(val) {
    minRating = val;
    render();
}

function useMyLocation() {
    if (!navigator.geolocation) {
        alert("La géolocalisation n'est pas supportée par votre navigateur.");
        return;
    }
    navigator.geolocation.getCurrentPosition(pos => {
        loadWorkshops({ lat: pos.coords.latitude, lng: pos.coords.longitude });
    }, err => {
        alert("Impossible d'obtenir votre position.");
    });
}

async function chooseWorkshop(wId) {
    try {
        const res = await fetch(`${API_BASE}/reparations/${repairId}/ateliers/choisir`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ workshop_id: wId })
        });
        if (!res.ok) throw new Error('Erreur lors du choix de l\'atelier');
        showToast("Atelier sélectionné avec succès !");
        setTimeout(() => {
            window.location.href = `/reparations/${repairId}`;
        }, 600);
    } catch(err) {
        showToast(err.message || 'Une erreur est survenue.', true);
    }
}

/* ================= CRUD ACTIONS (POPUP / MODALS) ================= */

// 1. DÉTAILS
function openDetailModal(wId) {
    const w = allWorkshops.find(x => x.id === wId);
    if (!w) return;

    document.getElementById('detName').textContent = w.name;
    const rating = Number(w.rating || 5).toFixed(1);
    document.getElementById('detRating').textContent = `★ ${rating} / 5`;
    document.getElementById('detAddress').textContent = w.address || 'Non renseignée';
    document.getElementById('detCity').textContent = w.city || 'Non renseignée';
    
    const phoneEl = document.getElementById('detPhone');
    if (w.phone) {
        phoneEl.innerHTML = `<a href="tel:${w.phone}" style="color:var(--green)">📞 ${w.phone}</a>`;
    } else {
        phoneEl.textContent = 'Non renseigné';
    }

    const specContainer = document.getElementById('detSpecialties');
    specContainer.innerHTML = (w.specialties || []).map(s => 
        `<span class="tag tag-green">${s}</span>`
    ).join('') || '<span class="tag">Général</span>';

    document.getElementById('btnDetToEdit').onclick = () => {
        closeDetailModal();
        openEditModal(wId);
    };
    document.getElementById('btnDetChoose').onclick = () => {
        closeDetailModal();
        chooseWorkshop(wId);
    };

    document.getElementById('detailWorkshopModal').style.display = 'flex';
}

function closeDetailModal() {
    document.getElementById('detailWorkshopModal').style.display = 'none';
}

// 2. MODIFIER
function openEditModal(wId) {
    const w = allWorkshops.find(x => x.id === wId);
    if (!w) return;

    document.getElementById('editWId').value = w.id;
    document.getElementById('editWName').value = w.name;
    document.getElementById('editWAddress').value = w.address;
    document.getElementById('editWCity').value = w.city || '';
    document.getElementById('editWPhone').value = w.phone || '';
    document.getElementById('editModalError').style.display = 'none';

    editSelSpecs = Array.isArray(w.specialties) ? [...w.specialties] : [];

    const chipsContainer = document.getElementById('editSpecialtyChips');
    chipsContainer.innerHTML = SPECIALTIES.map(s => {
        const isChecked = editSelSpecs.includes(s);
        return `<label class="tag ${isChecked ? 'tag-green' : ''}" onclick="toggleEditSpec('${s}', this)">
            <input type="checkbox" ${isChecked ? 'checked' : ''} style="accent-color:var(--green)"/> ${s}
        </label>`;
    }).join('');

    document.getElementById('editWorkshopModal').style.display = 'flex';
}

function toggleEditSpec(s, el) {
    if (editSelSpecs.includes(s)) {
        editSelSpecs = editSelSpecs.filter(x => x !== s);
        el.classList.remove('tag-green');
    } else {
        editSelSpecs.push(s);
        el.classList.add('tag-green');
    }
}

function closeEditModal() {
    document.getElementById('editWorkshopModal').style.display = 'none';
}

async function submitEditWorkshop() {
    const id = document.getElementById('editWId').value;
    const name = document.getElementById('editWName').value.trim();
    const address = document.getElementById('editWAddress').value.trim();
    const city = document.getElementById('editWCity').value.trim();
    const phone = document.getElementById('editWPhone').value.trim();
    const errEl = document.getElementById('editModalError');
    const btn = document.getElementById('btnSaveEditWorkshop');

    if (!name || !address) {
        errEl.textContent = "Le nom et l'adresse sont obligatoires.";
        errEl.style.display = 'block';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Enregistrement...';
    errEl.style.display = 'none';

    try {
        const res = await fetch(`${API_BASE}/workshops/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name,
                address,
                city: city || null,
                phone: phone || null,
                specialties: editSelSpecs.length ? editSelSpecs : ['Général']
            })
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur lors de la modification.');

        closeEditModal();
        showToast("Atelier modifié avec succès !");

        // Met à jour localement l'atelier dans allWorkshops
        const idx = allWorkshops.findIndex(x => x.id === parseInt(id));
        if (idx !== -1) {
            allWorkshops[idx] = { ...allWorkshops[idx], ...data.data };
        }
        initSpecialtiesFilter();
        render();
    } catch(err) {
        errEl.textContent = err.message || "Erreur lors de la mise à jour.";
        errEl.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Enregistrer les modifications';
    }
}

// 3. SUPPRIMER
function openDeleteModal(wId) {
    const w = allWorkshops.find(x => x.id === wId);
    if (!w) return;

    deletingWorkshopId = wId;
    document.getElementById('deleteWName').textContent = w.name;
    document.getElementById('deleteModalError').style.display = 'none';
    document.getElementById('deleteWorkshopModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteWorkshopModal').style.display = 'none';
    deletingWorkshopId = null;
}

async function submitDeleteWorkshop() {
    if (!deletingWorkshopId) return;

    const btn = document.getElementById('btnConfirmDelete');
    const errEl = document.getElementById('deleteModalError');

    btn.disabled = true;
    btn.textContent = 'Suppression...';
    errEl.style.display = 'none';

    try {
        const res = await fetch(`${API_BASE}/workshops/${deletingWorkshopId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        if (!res.ok) {
            const data = await res.json();
            throw new Error(data.message || 'Erreur lors de la suppression.');
        }

        allWorkshops = allWorkshops.filter(x => x.id !== deletingWorkshopId);
        closeDeleteModal();
        showToast("Atelier supprimé avec succès !");
        initSpecialtiesFilter();
        render();
    } catch(err) {
        errEl.textContent = err.message || "Impossible de supprimer cet atelier.";
        errEl.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = '🗑️ Supprimer';
    }
}

/* ================= RENDU DE LA LISTE ================= */
function render() {
    let list = allWorkshops;

    if (selectedSpecialties.length > 0) {
        list = list.filter(w => (w.specialties || []).some(s => selectedSpecialties.includes(s)));
    }

    if (minRating > 0) {
        list = list.filter(w => (w.rating || 0) >= minRating);
    }

    document.getElementById('foundCount').textContent = `${list.length} atelier(s) trouvé(s)`;

    const emptyEl = document.getElementById('emptyWorkshops');
    const listEl = document.getElementById('workshopsList');

    if (list.length === 0) {
        emptyEl.style.display = 'block';
        listEl.innerHTML = '';
        return;
    }

    emptyEl.style.display = 'none';
    listEl.innerHTML = list.map(w => {
        const rating = Number(w.rating || 5).toFixed(1);
        const distanceStr = (w.distance !== undefined && w.distance !== null)
            ? ` · ${Number(w.distance).toFixed(1)} km`
            : '';
        const tagsHtml = (w.specialties || []).map(spec =>
            `<span class="tag ${spec !== 'Général' ? 'tag-green' : ''}">${spec}</span>`
        ).join(' ');

        return `
            <div class="wcard" id="workshopCard-${w.id}">
                <div class="wcard-photo">🧵</div>
                <div class="wcard-body">
                    <div class="wcard-top">
                        <div>
                            <h3 style="margin-bottom:0.25rem">${w.name}</h3>
                            <div class="wcard-rating">
                                ★★★★★ <span>${rating}/5</span>
                            </div>
                        </div>
                        <div class="wcard-price">
                            ${averageCost} DT
                            <small>prix moyen</small>
                        </div>
                    </div>

                    <div class="wcard-meta">
                        📍 ${w.city || (w.address ? w.address : 'Non renseigné')}${distanceStr}
                        ${w.phone ? ` · 📞 ${w.phone}` : ''}
                    </div>

                    <div class="wcard-tags">
                        ${tagsHtml}
                    </div>

                    <div class="action-btn-group">
                        <button type="button" class="btn-choose" onclick="chooseWorkshop(${w.id})">
                            ✓ Choisir cet atelier
                        </button>
                        <button type="button" class="btn-act btn-act-detail" onclick="openDetailModal(${w.id})" title="Voir tous les détails">
                            👁️ Détails
                        </button>
                        <button type="button" class="btn-act btn-act-edit" onclick="openEditModal(${w.id})" title="Modifier les informations">
                            ✏️ Modifier
                        </button>
                        <button type="button" class="btn-act btn-act-delete" onclick="openDeleteModal(${w.id})" title="Supprimer cet atelier">
                            🗑️ Supprimer
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function onWorkshopSaved(newWorkshop) {
    showToast("Atelier ajouté avec succès !");
    loadWorkshops();
}

document.addEventListener('DOMContentLoaded', () => {
    loadWorkshops();
});
</script>
@endpush
