@extends('layouts.app')

@section('title', 'Analyse intelligente')

@section('content')
<div class="page">
    <div style="display:flex; justify-content:flex-end; align-items:center; gap:0.75rem; margin-bottom:1rem">
        <a href="{{ route('reparations.index') }}"
           style="width:auto; padding:0.55rem 1.1rem; font-size:0.85rem; font-weight:700;
                  border:1px solid #22c55e; border-radius:0.6rem; cursor:pointer;
                  background:transparent; color:#22c55e; white-space:nowrap; text-decoration:none">
            📋 Mes demandes
        </a>
        <button type="button" onclick="openWorkshopModal()"
                style="width:auto; padding:0.55rem 1.1rem; font-size:0.85rem; font-weight:700;
                       border:none; border-radius:0.6rem; cursor:pointer;
                       background:#22c55e; color:#fff; white-space:nowrap">
            + Ajouter un atelier
        </button>
    </div>

    <!-- Mode Standard : Formulaire de sélection -->
    <div id="createSection">
        <div style="text-align:center">
            <span class="pill"><span class="dot"></span> IA active</span>
            <h1 class="hero-title">Analyse intelligente</h1>
            <p class="hero-subtitle" style="margin-left:auto; margin-right:auto; text-align:center">
                Photographiez votre vêtement. En quelques secondes, l'IA identifie le
                défaut et vous propose la meilleure réparation.
            </p>
        </div>

        <div id="errorAlert" class="alert alert-error" style="display:none; margin-bottom:1.25rem"></div>

        <label class="dropzone" style="text-align:center; display:block">
            <input type="file" id="photoInput" accept="image/*" hidden onchange="onFileSelected(event)" />
            <div id="dropzoneEmpty">
                <span class="btn-pick">📷 Prendre une photo</span>
            </div>
            <div id="dropzonePreview" style="display:none">
                <img id="previewImg" src="" alt="Aperçu" />
                <p style="margin-top:1rem; color:var(--text-dim); font-size:0.85rem">
                    Cliquez pour changer de photo
                </p>
            </div>
        </label>

        <button id="btnSubmit" class="btn-cta" style="margin-top:1.25rem; display:none" onclick="submitAnalysis()">
            🤖 Analyser avec l'IA
        </button>

        <div class="examples-title">Ou essayez avec un exemple</div>
        <div class="examples-grid">
            <div class="example-card" onclick="useExample('Jean avec trou', '/examples/jean-trou.jpg')">
                <img src="/examples/jean-trou.jpg" alt="Jean avec trou" />
                <div class="example-overlay">
                    <div class="name">Jean avec trou</div>
                    <div class="cta">Analyser →</div>
                </div>
            </div>
            <div class="example-card" onclick="useExample('T-shirt taché', '/examples/tshirt-tache.jpg')">
                <img src="/examples/tshirt-tache.jpg" alt="T-shirt taché" />
                <div class="example-overlay">
                    <div class="name">T-shirt taché</div>
                    <div class="cta">Analyser →</div>
                </div>
            </div>
            <div class="example-card" onclick="useExample('Vêtement déchiré', '/examples/chemise-dechirure.jpg')">
                <img src="/examples/chemise-dechirure.jpg" alt="Vêtement déchiré" />
                <div class="example-overlay">
                    <div class="name">Vêtement déchiré</div>
                    <div class="cta">Analyser →</div>
                </div>
            </div>
        </div>

        <div class="defect-grid-title">L'IA peut détecter</div>
        <div class="defect-grid">
            <div class="defect-chip"><span class="dot dot-red"></span> Trou</div>
            <div class="defect-chip"><span class="dot dot-red"></span> Déchirure</div>
            <div class="defect-chip"><span class="dot dot-orange"></span> Tache</div>
            <div class="defect-chip"><span class="dot dot-orange"></span> Fermeture cassée</div>
            <div class="defect-chip"><span class="dot dot-orange"></span> Bouton manquant</div>
            <div class="defect-chip"><span class="dot dot-yellow"></span> Usure</div>
        </div>
    </div>

    <!-- Mode Scan / Analyse en cours -->
    <div id="analyzingSection" style="display:none">
        <div class="analyzing-photo-solo">
            <div class="scan-line"></div>
            <img id="scanPreviewImg" src="" alt="Vêtement en cours d'analyse" />
            <div class="analyzing-status-pill">
                <span class="dot"></span> <span id="stepLabel">Chargement de l'image...</span>
            </div>
        </div>

        <div class="big-percent"><span id="percentText">17</span><span class="sign">%</span></div>
        <p class="analyzing-caption">Analyse en cours...</p>

        <div class="analyzing-progress-track">
            <div id="progressBar" class="analyzing-progress-fill" style="width: 17%"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedFile = null;
let currentStep = 0;
let stepTimer = null;

const STEPS = [
    "Chargement de l'image...",
    "Détection des zones d'usure...",
    "Analyse des textures de tissu...",
    "Classification du défaut...",
    "Estimation du coût de réparation...",
    "Rapport prêt."
];

function showError(msg) {
    const errEl = document.getElementById('errorAlert');
    if (msg) {
        errEl.textContent = msg;
        errEl.style.display = 'block';
    } else {
        errEl.style.display = 'none';
    }
}

function onFileSelected(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    setFile(file);
}

function setFile(file) {
    selectedFile = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        const url = e.target.result;
        document.getElementById('previewImg').src = url;
        document.getElementById('scanPreviewImg').src = url;
        document.getElementById('dropzoneEmpty').style.display = 'none';
        document.getElementById('dropzonePreview').style.display = 'block';
        document.getElementById('btnSubmit').style.display = 'block';
    };
    reader.readAsDataURL(file);
}

async function useExample(name, path) {
    try {
        const res = await fetch(path);
        if (!res.ok) throw new Error('Image non trouvée');
        const blob = await res.blob();
        const file = new File([blob], name + '.jpg', { type: blob.type || 'image/jpeg' });
        setFile(file);
        submitAnalysis();
    } catch(err) {
        showError("Impossible de charger l'image d'exemple.");
    }
}

function updateProgress(stepIdx) {
    currentStep = stepIdx;
    const pct = Math.round(((stepIdx + 1) / STEPS.length) * 100);
    document.getElementById('stepLabel').textContent = STEPS[stepIdx];
    document.getElementById('percentText').textContent = pct;
    document.getElementById('progressBar').style.width = pct + '%';
}

async function submitAnalysis() {
    if (!selectedFile) {
        showError('Merci de sélectionner une photo.');
        return;
    }

    showError(null);
    document.getElementById('createSection').style.display = 'none';
    document.getElementById('analyzingSection').style.display = 'block';
    updateProgress(0);

    stepTimer = setInterval(() => {
        if (currentStep < STEPS.length - 2) {
            updateProgress(currentStep + 1);
        }
    }, 550);

    const formData = new FormData();
    formData.append('photo', selectedFile);

    try {
        const res = await fetch(`${API_BASE}/repairs`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || "Erreur lors de l'analyse. Réessayez.");

        updateProgress(STEPS.length - 1);
        clearInterval(stepTimer);

        setTimeout(() => {
            window.location.href = `/reparations/${data.data.id}`;
        }, 400);
    } catch (err) {
        clearInterval(stepTimer);
        document.getElementById('analyzingSection').style.display = 'none';
        document.getElementById('createSection').style.display = 'block';
        showError(err.message || "Erreur lors de l'analyse. Réessayez.");
    }
}
</script>
@endpush
