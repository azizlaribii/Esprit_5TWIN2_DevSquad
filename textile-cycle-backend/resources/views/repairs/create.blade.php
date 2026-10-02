@extends('layouts.app')

@section('title', 'Analyse Intelligente de Vêtement')
@section('meta_description', 'Photographiez votre vêtement endommagé pour identifier le défaut et trouver la réparation adaptée')
@section('breadcrumb', 'Gestion › Réparations › Analyse IA')

@section('content')
<div class="animate-fade-in-up" style="max-width:900px; margin:0 auto">

    <!-- Header navigation -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem">
        <div>
            <h1 class="page-title" style="display:flex; align-items:center; gap:0.5rem">
                <span class="material-icons-round" style="color:var(--primary-light)">auto_awesome</span>
                Diagnostic & Réparation Intelligente
            </h1>
            <p class="page-subtitle">Détection automatisée des défauts textiles par intelligence artificielle</p>
        </div>
        <div style="display:flex; gap:0.75rem">
            <a href="{{ route('reparations.index') }}" class="btn btn-secondary">
                <span class="material-icons-round">arrow_back</span> Suivi Réparations
            </a>
            <button type="button" onclick="openWorkshopModal()" class="btn btn-primary">
                <span class="material-icons-round">add_business</span> Ajouter un atelier
            </button>
        </div>
    </div>

    <!-- Mode Standard : Formulaire de scan & sélection -->
    <div id="createSection" class="card" style="padding:2rem">
        <div style="text-align:center; margin-bottom:2rem">
            <div style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.35rem 0.85rem; border-radius:999px; background:rgba(67,217,173,0.12); border:1px solid rgba(67,217,173,0.3); color:var(--secondary); font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:1rem">
                <span style="width:8px; height:8px; border-radius:50%; background:var(--secondary); box-shadow:0 0 8px var(--secondary)"></span>
                Service IA Actif & Prêt
            </div>
            <h2 style="font-size:1.6rem; font-family:'Outfit',sans-serif; margin-bottom:0.5rem">
                Photographiez votre pièce textile
            </h2>
            <p style="color:var(--text-secondary); font-size:0.95rem; max-width:580px; margin:0 auto">
                Téléversez une photo ou utilisez votre appareil. Notre modèle identifie le type d'altération (trou, déchirure, tache, fermeture...) et estime les coûts de retouche.
            </p>
        </div>

        <div id="errorAlert" class="alert alert-danger" style="display:none; margin-bottom:1.5rem; padding:0.85rem 1rem; border-radius:10px; background:rgba(255,101,132,0.15); color:var(--accent-red); border:1px solid rgba(255,101,132,0.3)"></div>

        <!-- Zone de dépôt / Prise de vue -->
        <label class="dropzone-box" style="border:2px dashed var(--border-active); border-radius:16px; padding:3rem 1.5rem; text-align:center; cursor:pointer; background:rgba(17,24,39,0.6); display:block; transition:var(--transition); position:relative">
            <input type="file" id="photoInput" accept="image/*" hidden onchange="onFileSelected(event)" />
            
            <div id="dropzoneEmpty">
                <div style="width:64px; height:64px; border-radius:50%; background:rgba(108,99,255,0.15); display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem; color:var(--primary-light)">
                    <span class="material-icons-round" style="font-size:2rem">add_photo_alternate</span>
                </div>
                <div style="font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-bottom:0.5rem">
                    Déposez une photo ici ou cliquez pour parcourir
                </div>
                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1.5rem">
                    Formats acceptés : JPG, PNG, WEBP (Max 5 Mo)
                </p>
                <div style="display:inline-flex; gap:0.75rem">
                    <span class="btn btn-primary" style="pointer-events:none">
                        <span class="material-icons-round">photo_camera</span> Choisir une photo
                    </span>
                </div>
            </div>

            <div id="dropzonePreview" style="display:none">
                <div style="position:relative; display:inline-block; max-width:400px; border-radius:12px; overflow:hidden; border:2px solid var(--primary); box-shadow:var(--shadow-glow)">
                    <img id="previewImg" src="" alt="Aperçu du vêtement" style="width:100%; display:block; max-height:360px; object-fit:contain; background:#000" />
                </div>
                <p style="margin-top:1rem; color:var(--primary-light); font-size:0.85rem; font-weight:600">
                    <span class="material-icons-round" style="font-size:1rem; vertical-align:middle">refresh</span> Cliquez pour choisir une autre image
                </p>
            </div>
        </label>

        <!-- Bouton Analyser -->
        <button id="btnSubmit" class="btn btn-primary" style="width:100%; justify-content:center; padding:1rem; font-size:1.05rem; margin-top:1.5rem; display:none" onclick="submitAnalysis()">
            <span class="material-icons-round">psychology</span> Lancer l'analyse du défaut avec l'IA
        </button>

        <!-- Exemples pré-enregistrés -->
        <div style="margin-top:2.5rem; border-top:1px solid var(--border); padding-top:1.5rem">
            <div style="font-family:'Outfit',sans-serif; font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); margin-bottom:1rem; text-align:center">
                Ou essayez immédiatement avec nos exemples :
            </div>
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem">
                <div class="card example-box" style="padding:0; overflow:hidden; cursor:pointer; border:1px solid var(--border)" onclick="useExample('Jean troué', '/examples/jean-trou.jpg')">
                    <img src="/examples/jean-trou.jpg" alt="Jean avec trou" style="width:100%; height:120px; object-fit:cover" onerror="this.src='https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=400'" />
                    <div style="padding:0.75rem; text-align:center">
                        <div style="font-weight:700; font-size:0.85rem">Jean avec trou</div>
                        <div style="color:var(--secondary); font-size:0.75rem; font-weight:600; margin-top:0.25rem">Tester →</div>
                    </div>
                </div>
                <div class="card example-box" style="padding:0; overflow:hidden; cursor:pointer; border:1px solid var(--border)" onclick="useExample('T-shirt taché', '/examples/tshirt-tache.jpg')">
                    <img src="/examples/tshirt-tache.jpg" alt="T-shirt taché" style="width:100%; height:120px; object-fit:cover" onerror="this.src='https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=400'" />
                    <div style="padding:0.75rem; text-align:center">
                        <div style="font-weight:700; font-size:0.85rem">T-shirt taché</div>
                        <div style="color:var(--secondary); font-size:0.75rem; font-weight:600; margin-top:0.25rem">Tester →</div>
                    </div>
                </div>
                <div class="card example-box" style="padding:0; overflow:hidden; cursor:pointer; border:1px solid var(--border)" onclick="useExample('Vêtement déchiré', '/examples/chemise-dechirure.jpg')">
                    <img src="/examples/chemise-dechirure.jpg" alt="Vêtement déchiré" style="width:100%; height:120px; object-fit:cover" onerror="this.src='https://images.unsplash.com/photo-1598033129183-c4f50c736f10?w=400'" />
                    <div style="padding:0.75rem; text-align:center">
                        <div style="font-weight:700; font-size:0.85rem">Chemise déchirée</div>
                        <div style="color:var(--secondary); font-size:0.75rem; font-weight:600; margin-top:0.25rem">Tester →</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Défauts pris en charge -->
        <div style="margin-top:2rem; padding:1.25rem; background:rgba(255,255,255,0.02); border-radius:12px; border:1px solid var(--border)">
            <div style="font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); margin-bottom:0.75rem">
                Défauts reconnus par le modèle :
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:0.5rem">
                <span class="badge" style="background:rgba(255,101,132,0.15); color:var(--accent-red); border:1px solid rgba(255,101,132,0.3)">🔴 Trou</span>
                <span class="badge" style="background:rgba(255,101,132,0.15); color:var(--accent-red); border:1px solid rgba(255,101,132,0.3)">🔴 Déchirure</span>
                <span class="badge" style="background:rgba(255,167,38,0.15); color:var(--accent-orange); border:1px solid rgba(255,167,38,0.3)">🟠 Tache</span>
                <span class="badge" style="background:rgba(255,167,38,0.15); color:var(--accent-orange); border:1px solid rgba(255,167,38,0.3)">🟠 Fermeture cassée</span>
                <span class="badge" style="background:rgba(255,167,38,0.15); color:var(--accent-orange); border:1px solid rgba(255,167,38,0.3)">🟠 Bouton manquant</span>
                <span class="badge" style="background:rgba(67,217,173,0.15); color:var(--secondary); border:1px solid rgba(67,217,173,0.3)">🟡 Usure & Décoloration</span>
            </div>
        </div>
    </div>

    <!-- Mode Scan / Analyse en direct -->
    <div id="analyzingSection" class="card" style="display:none; text-align:center; padding:3rem 1.5rem">
        <div style="position:relative; max-width:440px; margin:0 auto 1.5rem; border-radius:16px; overflow:hidden; border:2px solid var(--primary); box-shadow:var(--shadow-glow)">
            <div id="scanLine" style="position:absolute; left:0; right:0; height:3px; background:linear-gradient(90deg,transparent,var(--secondary),transparent); box-shadow:0 0 16px 3px var(--secondary); animation:scan-move 1.8s ease-in-out infinite; z-index:10"></div>
            <img id="scanPreviewImg" src="" alt="Vêtement en cours d'analyse" style="width:100%; display:block; max-height:400px; object-fit:cover; filter:brightness(0.85)" />
            <div style="position:absolute; bottom:14px; left:50%; transform:translateX(-50%); background:rgba(10,14,26,0.9); border:1px solid var(--border); border-radius:999px; padding:0.45rem 1rem; font-size:0.85rem; display:flex; align-items:center; gap:0.5rem; white-space:nowrap; z-index:11">
                <span style="width:8px; height:8px; border-radius:50%; background:var(--secondary); box-shadow:0 0 6px var(--secondary)"></span>
                <span id="stepLabel">Chargement de l'image...</span>
            </div>
        </div>

        <div style="font-family:'Outfit',sans-serif; font-size:3.5rem; font-weight:800; color:var(--secondary); line-height:1; margin-bottom:0.5rem">
            <span id="percentText">20</span>%
        </div>
        <p style="color:var(--text-secondary); font-size:0.95rem; margin-bottom:1.5rem">Diagnostic IA en cours d'exécution...</p>

        <div style="max-width:440px; margin:0 auto; height:8px; border-radius:999px; background:rgba(255,255,255,0.08); overflow:hidden">
            <div id="progressBar" style="height:100%; width:20%; background:var(--gradient-green); border-radius:999px; transition:width 0.3s ease"></div>
        </div>
    </div>

</div>

<style>
@keyframes scan-move {
    0% { top: 6%; }
    50% { top: 90%; }
    100% { top: 6%; }
}
.dropzone-box:hover {
    border-color: var(--primary) !important;
    background: rgba(108,99,255,0.05) !important;
}
.example-box:hover {
    transform: translateY(-4px);
    border-color: var(--primary) !important;
    box-shadow: var(--shadow-glow);
}
</style>
@endsection

@push('scripts')
<script>
let selectedFile = null;
let currentStep = 0;
let stepTimer = null;

const STEPS = [
    "Chargement de l'image...",
    "Détection des zones d'altération...",
    "Analyse de la trame textile...",
    "Classification du défaut...",
    "Estimation du coût d'intervention...",
    "Diagnostic prêt."
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
        document.getElementById('btnSubmit').style.display = 'inline-flex';
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
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const res = await fetch('/api/repairs', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                ...(csrf ? {'X-CSRF-TOKEN': csrf} : {})
            },
            body: formData
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || "Erreur lors de l'analyse.");

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
