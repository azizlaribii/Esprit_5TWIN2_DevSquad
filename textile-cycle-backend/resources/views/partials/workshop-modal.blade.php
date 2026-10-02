<!-- Modal Ajouter un atelier -->
<div id="workshopModal" class="modal-backdrop" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.75); align-items:center; justify-content:center; z-index:1000; backdrop-filter:blur(4px)" onclick="if(event.target===this)closeWorkshopModal()">
    <div class="modal-box" style="background:var(--bg-surface); border:1px solid var(--border); border-radius:16px; padding:24px; width:92%; max-width:520px; max-height:90vh; overflow-y:auto; color:var(--text-primary); box-shadow:var(--shadow-glow)">
        <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px">
            <h2 style="margin:0; font-size:1.25rem; font-family:'Outfit',sans-serif">Ajouter un atelier partenaire</h2>
            <button type="button" class="modal-close" onclick="closeWorkshopModal()" style="background:none; border:none; color:var(--text-muted); font-size:22px; cursor:pointer">&times;</button>
        </div>
        <div id="modalError" class="alert alert-danger" style="display:none; margin-bottom:1rem; padding:0.6rem 0.8rem; border-radius:8px; font-size:0.85rem; background:rgba(255,101,132,0.15); color:var(--accent-red); border:1px solid rgba(255,101,132,0.3)"></div>
        
        <div style="display:flex; flex-direction:column; gap:1rem">
            <label style="display:flex; flex-direction:column; gap:.35rem; font-size:.85rem; font-weight:600; color:var(--text-secondary)">
                Nom de l'atelier *
                <input type="text" id="wName" placeholder="Ex: Atelier Couture Circulaire" style="padding:.65rem .85rem; border:1px solid var(--border); border-radius:8px; background:var(--bg-card); color:var(--text-primary); font-family:inherit"/>
            </label>
            <label style="display:flex; flex-direction:column; gap:.35rem; font-size:.85rem; font-weight:600; color:var(--text-secondary)">
                Adresse complète *
                <input type="text" id="wAddress" placeholder="Ex: 15 rue des Retoucheurs" style="padding:.65rem .85rem; border:1px solid var(--border); border-radius:8px; background:var(--bg-card); color:var(--text-primary); font-family:inherit"/>
            </label>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem">
                <label style="display:flex; flex-direction:column; gap:.35rem; font-size:.85rem; font-weight:600; color:var(--text-secondary)">
                    Ville
                    <input type="text" id="wCity" placeholder="Ex: Paris" style="padding:.65rem .85rem; border:1px solid var(--border); border-radius:8px; background:var(--bg-card); color:var(--text-primary); font-family:inherit"/>
                </label>
                <label style="display:flex; flex-direction:column; gap:.35rem; font-size:.85rem; font-weight:600; color:var(--text-secondary)">
                    Téléphone
                    <input type="text" id="wPhone" placeholder="+33 1 23 45 67 89" style="padding:.65rem .85rem; border:1px solid var(--border); border-radius:8px; background:var(--bg-card); color:var(--text-primary); font-family:inherit"/>
                </label>
            </div>
            <div>
                <p style="font-size:.85rem; font-weight:600; color:var(--text-secondary); margin-bottom:.5rem">Spécialités</p>
                <div id="specialtyChips" style="display:flex; flex-wrap:wrap; gap:.5rem"></div>
            </div>
        </div>

        <div style="display:flex; gap:.75rem; margin-top:1.5rem">
            <button type="button" class="btn btn-secondary" style="flex:1; justify-content:center" onclick="closeWorkshopModal()">Annuler</button>
            <button type="button" class="btn btn-primary" id="btnSaveWorkshop" style="flex:1; justify-content:center" onclick="saveWorkshop()">Enregistrer l'atelier</button>
        </div>
    </div>
</div>

<script>
window.API_BASE = window.API_BASE || '/api';
const MODAL_SPECIALTIES = ['Trou', 'Déchirure', 'Tache', 'Fermeture cassée', 'Bouton manquant', 'Usure', 'Couture', 'Cuir', 'Nettoyage spécialisé'];
let selSpecsModal = [];

function openWorkshopModal(){
    const nameEl = document.getElementById('wName');
    const addrEl = document.getElementById('wAddress');
    const cityEl = document.getElementById('wCity');
    const phoneEl = document.getElementById('wPhone');
    const errEl = document.getElementById('modalError');
    if (nameEl) nameEl.value = '';
    if (addrEl) addrEl.value = '';
    if (cityEl) cityEl.value = '';
    if (phoneEl) phoneEl.value = '';
    selSpecsModal = [];
    if (errEl) errEl.style.display = 'none';

    const chipsEl = document.getElementById('specialtyChips');
    if (chipsEl) {
        chipsEl.innerHTML = MODAL_SPECIALTIES.map(s => 
            `<button type="button" class="badge badge-secondary spec-btn" style="cursor:pointer; padding:0.4rem 0.75rem; font-size:0.75rem; border:1px solid var(--border); background:var(--bg-card); color:var(--text-secondary)" onclick="toggleSpecModal('${s}', this)">${s}</button>`
        ).join('');
    }
    const modal = document.getElementById('workshopModal');
    if (modal) modal.style.display = 'flex';
}

function toggleSpecModal(s, el){
    if (selSpecsModal.includes(s)) {
        selSpecsModal = selSpecsModal.filter(x => x !== s);
        el.style.background = 'var(--bg-card)';
        el.style.color = 'var(--text-secondary)';
        el.style.borderColor = 'var(--border)';
    } else {
        selSpecsModal.push(s);
        el.style.background = 'rgba(108,99,255,0.2)';
        el.style.color = 'var(--primary-light)';
        el.style.borderColor = 'var(--primary)';
    }
}

function closeWorkshopModal(){
    const modal = document.getElementById('workshopModal');
    if (modal) modal.style.display = 'none';
}

async function saveWorkshop(){
    const name = document.getElementById('wName')?.value.trim();
    const address = document.getElementById('wAddress')?.value.trim();
    const errEl = document.getElementById('modalError');
    const btn = document.getElementById('btnSaveWorkshop');
    if (!name || !address) {
        if (errEl) {
            errEl.textContent = "Le nom et l'adresse sont obligatoires.";
            errEl.style.display = 'block';
        }
        return;
    }
    if (btn) { btn.disabled = true; btn.textContent = 'Enregistrement...'; }
    if (errEl) errEl.style.display = 'none';

    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const res = await fetch(`${API_BASE}/workshops`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...(csrf ? {'X-CSRF-TOKEN': csrf} : {})
            },
            body: JSON.stringify({
                name,
                address,
                city: document.getElementById('wCity')?.value.trim() || undefined,
                phone: document.getElementById('wPhone')?.value.trim() || undefined,
                specialties: selSpecsModal.length ? selSpecsModal : ['Général']
            })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur lors de la création de l\'atelier');
        closeWorkshopModal();
        if (typeof onWorkshopSaved === 'function') {
            onWorkshopSaved(data.data);
        } else {
            alert('Atelier ajouté avec succès !');
        }
    } catch(err) {
        if (errEl) {
            errEl.textContent = err.message || "Erreur lors de la création.";
            errEl.style.display = 'block';
        }
    } finally {
        if (btn) { btn.disabled = false; btn.textContent = "Enregistrer l'atelier"; }
    }
}
</script>
