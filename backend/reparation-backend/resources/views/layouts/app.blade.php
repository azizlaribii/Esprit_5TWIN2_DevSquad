<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Reparation intelligente — analysez vos vetements avec l IA')">
    <title>@yield('title', 'Reparation Intelligente') — TextileCycle</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#0d0b17;--bg-elevated:#171326;--bg-card:#171326;--bg-sidebar:#120f1e;--border:#2a2440;--border-soft:#201b33;--text:#f4f2f8;--text-muted:#9b93ad;--text-dim:#635c78;--green:#22c55e;--green-dark:#14532d;--green-bg:#0f2a1a;--purple:#8b5cf6;--pink:#ec4899;--accent-gradient:linear-gradient(135deg,var(--purple),var(--pink));--red:#f43f5e;--orange:#fb923c;--yellow:#facc15;--font-mono:'JetBrains Mono','Courier New',monospace;--font-serif:Georgia,'Times New Roman',serif;--font-body:'Inter',-apple-system,'Segoe UI',Roboto,Arial,sans-serif;--radius:14px;--btn-text-on-green:#ffffff}
        *{box-sizing:border-box}html,body{margin:0;min-height:100%;background:var(--bg);color:var(--text);font-family:var(--font-body)}a{color:var(--purple);text-decoration:none}a:hover{text-decoration:underline}button{cursor:pointer;font-family:inherit}ul{padding-left:0;list-style:none;margin:0}
        .page{max-width:980px;margin:0 auto;padding:2rem 1.25rem 4rem}.page-wide{max-width:1240px;margin:0 auto;padding:2rem 1.25rem 4rem}
        .navbar{background:var(--bg-elevated);border-bottom:1px solid var(--border);padding:0.9rem 1.5rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
        .navbar .brand{font-weight:700;color:var(--text);font-family:var(--font-serif);font-size:1.05rem}.navbar .brand:hover{text-decoration:none}
        .navbar nav{display:flex;gap:1.5rem;align-items:center;font-size:0.88rem}.navbar nav a{color:var(--text-muted)}.navbar nav a:hover{color:var(--text);text-decoration:none}.navbar nav a.active{color:var(--text)}
        .back-link{display:inline-flex;align-items:center;gap:0.4rem;background:var(--bg-card);border:1px solid var(--border);color:var(--text);padding:0.5rem 1rem;border-radius:999px;font-size:0.85rem;font-weight:600;margin-bottom:1.5rem}
        .back-link:hover{text-decoration:none;border-color:var(--green-dark);color:var(--text)}
        .pill{display:inline-flex;align-items:center;gap:0.5rem;font-family:var(--font-mono);font-size:0.72rem;letter-spacing:0.05em;text-transform:uppercase;padding:0.35rem 0.85rem;border-radius:999px;border:1px solid var(--purple);background:rgba(139,92,246,0.12);color:#c4b5fd;margin-bottom:1.25rem}
        .pill .dot{width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 6px var(--green)}
        h1.hero-title{font-family:var(--font-serif);font-weight:700;font-size:2.4rem;margin:0 0 0.6rem}.hero-subtitle{color:var(--text-muted);font-size:1rem;line-height:1.5;max-width:640px;margin:0 0 2rem}
        .panel{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem}
        .btn-cta{width:100%;background:var(--accent-gradient);color:#fff;border:none;padding:0.85rem;border-radius:0.7rem;font-weight:700;font-size:0.95rem;text-align:center;display:block;margin-top:0.5rem;cursor:pointer}.btn-cta:hover{filter:brightness(1.08);text-decoration:none;color:#fff}.btn-cta:disabled{opacity:0.6;cursor:not-allowed}
        .btn-secondary{width:100%;background:transparent;color:var(--text-muted);border:1px solid var(--border);padding:0.85rem;border-radius:0.7rem;font-weight:600;font-size:0.9rem;text-align:center;display:block;margin-top:0.6rem;cursor:pointer}.btn-secondary:hover{border-color:var(--text-dim);color:var(--text);text-decoration:none}
        .btn-pick{background:var(--accent-gradient);color:#fff;border:none;padding:0.7rem 1.4rem;border-radius:0.65rem;font-weight:700;font-size:0.9rem;display:inline-flex;align-items:center;gap:0.5rem;cursor:pointer}.btn-pick:hover{filter:brightness(1.08);text-decoration:none;color:#fff}
        .btn-choose{background:var(--accent-gradient);color:#fff;border:none;padding:0.55rem 1rem;border-radius:0.55rem;font-size:0.82rem;font-weight:700;cursor:pointer}.btn-choose:hover{filter:brightness(1.08)}
        .btn-outline{background:transparent;border:1px solid var(--border);color:var(--text);padding:0.55rem 1rem;border-radius:0.55rem;font-size:0.82rem;font-weight:600;cursor:pointer}.btn-outline:hover{border-color:var(--text-dim)}
        .alert{padding:0.65rem 0.9rem;border-radius:0.6rem;font-size:0.85rem}.alert-error{background:rgba(244,63,94,0.1);color:#fca5b1;border:1px solid rgba(244,63,94,0.3)}
        .dropzone{border:1px dashed var(--border);border-radius:var(--radius);padding:4rem 1.5rem;text-align:center;cursor:pointer;background:repeating-linear-gradient(180deg,transparent,transparent 27px,var(--border-soft) 28px),var(--bg-card);display:block;transition:border-color 0.15s}.dropzone:hover{border-color:var(--green-dark)}.dropzone img{max-height:280px;border-radius:0.75rem;margin:0 auto}
        .defect-grid-title{font-family:var(--font-mono);font-size:0.72rem;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-dim);margin:2rem 0 1rem}.defect-grid{display:grid;grid-template-columns:1fr 1fr;gap:0.75rem}@media(max-width:640px){.defect-grid{grid-template-columns:1fr}}.defect-chip{background:var(--bg-card);border:1px solid var(--border);border-radius:0.75rem;padding:0.8rem 1rem;display:flex;align-items:center;gap:0.7rem;font-size:0.92rem}.defect-chip .dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}.dot-red{background:var(--red)}.dot-orange{background:var(--orange)}.dot-yellow{background:var(--yellow)}
        .examples-title{text-align:center;font-family:var(--font-mono);font-size:0.72rem;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-dim);margin:2rem 0 1rem}.examples-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:0.9rem}@media(max-width:640px){.examples-grid{grid-template-columns:1fr}}.example-card{position:relative;border-radius:0.85rem;overflow:hidden;border:1px solid var(--border);cursor:pointer;aspect-ratio:4/3;background:#1a2230}.example-card img{width:100%;height:100%;object-fit:cover;display:block;transition:transform 0.2s}.example-card:hover img{transform:scale(1.04)}.example-overlay{position:absolute;left:0;right:0;bottom:0;padding:0.75rem 0.85rem;background:linear-gradient(to top,rgba(0,0,0,0.85),transparent)}.example-overlay .name{font-weight:700;font-size:0.9rem}.example-overlay .cta{color:var(--green);font-size:0.8rem;font-weight:600}
        .analyzing-photo-solo{position:relative;max-width:520px;margin:0 auto;border-radius:var(--radius);overflow:hidden;border:1px solid var(--border)}.analyzing-photo-solo img{width:100%;display:block;max-height:460px;object-fit:cover;filter:saturate(0.9) brightness(0.85)}
        .scan-line{position:absolute;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--green),transparent);box-shadow:0 0 12px 2px var(--green);animation:scan-move 1.8s ease-in-out infinite}@keyframes scan-move{0%{top:8%}50%{top:88%}100%{top:8%}}
        .analyzing-status-pill{position:absolute;left:50%;bottom:14px;transform:translateX(-50%);background:rgba(10,15,10,0.85);border:1px solid var(--border);border-radius:999px;padding:0.5rem 1rem;font-size:0.85rem;display:flex;align-items:center;gap:0.5rem;white-space:nowrap}.analyzing-status-pill .dot{width:8px;height:8px;border-radius:50%;background:var(--green);box-shadow:0 0 6px var(--green)}
        .big-percent{text-align:center;margin:1.75rem 0 0.4rem;font-family:var(--font-serif);font-weight:700;font-size:3.5rem;color:var(--green);line-height:1}.big-percent .sign{font-size:1.6rem;color:var(--text-dim);font-family:var(--font-body)}.analyzing-caption{text-align:center;color:var(--text-dim);font-size:0.9rem;margin-bottom:1.5rem}
        .analyzing-progress-track{max-width:520px;margin:0 auto;height:6px;border-radius:999px;background:var(--border);overflow:hidden}.analyzing-progress-fill{height:100%;background:linear-gradient(90deg,var(--green),#9ee6b0);border-radius:999px;transition:width 0.3s ease}
        .analyze-grid{display:grid;grid-template-columns:1.1fr 1fr;gap:1.5rem}@media(max-width:720px){.analyze-grid{grid-template-columns:1fr}}
        .analyze-photo-wrap{position:relative;border-radius:var(--radius);overflow:hidden;border:1px solid var(--border);margin-bottom:1rem}.analyze-photo-wrap img{width:100%;display:block;height:auto}
        .confidence-badge{position:absolute;bottom:12px;right:12px;background:rgba(10,15,10,0.9);border:1px solid var(--border);border-radius:0.6rem;padding:0.5rem 0.8rem;text-align:right}.confidence-badge .label{font-family:var(--font-mono);font-size:0.62rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.05em}.confidence-badge .value{font-weight:700;color:var(--green);font-size:1.05rem}
        .ai-header{display:flex;align-items:center;gap:0.65rem;margin-bottom:1.25rem}.ai-avatar{width:34px;height:34px;border-radius:0.6rem;background:var(--green-bg);border:1px solid var(--green-dark);display:flex;align-items:center;justify-content:center;font-size:1rem}.ai-header .title{font-family:var(--font-mono);font-size:0.78rem;letter-spacing:0.05em;color:var(--green);text-transform:uppercase}.ai-header .subtitle{font-size:0.75rem;color:var(--text-dim)}
        .section-label{font-size:0.72rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.06em;font-family:var(--font-mono);margin-bottom:0.35rem}
        .defect-title{font-size:1.5rem;font-weight:700;display:flex;align-items:center;gap:0.5rem;margin:0 0 1.25rem}
        .mini-card-row{display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1.25rem}.mini-card{background:var(--bg-elevated);border:1px solid var(--border);border-radius:0.75rem;padding:0.75rem 0.9rem}.mini-card .val{font-weight:600;margin-top:0.15rem}
        .repair-list li{display:flex;align-items:center;gap:0.6rem;background:rgba(139,92,246,0.08);border:1px solid rgba(139,92,246,0.3);border-radius:0.6rem;padding:0.6rem 0.9rem;margin-bottom:0.5rem;font-size:0.9rem}.repair-list li::before{content:'';width:6px;height:6px;border-radius:50%;background:var(--purple);flex-shrink:0}
        .cost-row{display:flex;align-items:center;justify-content:space-between;background:var(--bg-elevated);border:1px solid var(--border);border-radius:0.75rem;padding:0.9rem 1.1rem;margin:1rem 0}.cost-row .label{color:var(--text-dim);font-size:0.85rem}.cost-row .value{color:var(--green);font-weight:700;font-size:1.15rem}
        .workshop-selected{background:rgba(34,197,94,0.1);border:1px solid var(--green-dark);color:#4ade80;border-radius:0.6rem;padding:0.7rem 0.9rem;font-size:0.9rem;margin-top:0.75rem}
        .certainty-title{font-family:var(--font-mono);font-size:0.72rem;letter-spacing:0.06em;text-transform:uppercase;color:var(--text-dim);margin-bottom:0.9rem}.certainty-row{margin-bottom:0.75rem}.certainty-row .top{display:flex;justify-content:space-between;font-size:0.85rem;margin-bottom:0.3rem}.certainty-track{height:5px;background:var(--border);border-radius:999px;overflow:hidden}.certainty-fill{height:100%;border-radius:999px}
        .bbox-frame{position:absolute;border:1.5px solid var(--red);border-radius:4px;box-shadow:0 0 0 4000px rgba(0,0,0,0.15) inset}.bbox-label{position:absolute;top:-26px;left:0;background:var(--red);color:#fff;font-family:var(--font-mono);font-size:0.65rem;letter-spacing:0.04em;text-transform:uppercase;padding:0.25rem 0.55rem;border-radius:0.3rem;white-space:nowrap}
        .defect-icon-row{display:flex;align-items:center;gap:0.9rem;margin-bottom:1rem}.defect-icon-box{width:46px;height:46px;border-radius:0.7rem;background:var(--bg-elevated);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}.defect-icon-row .defect-name{font-size:1.35rem;font-weight:700}.defect-icon-row .defect-loc{color:var(--text-dim);font-size:0.85rem}.defect-icon-row .defect-loc strong{color:var(--text-muted)}.divider{border-top:1px solid var(--border);margin:1rem 0}
        .workshops-layout{display:grid;grid-template-columns:240px 1fr;gap:1.5rem;align-items:start}@media(max-width:800px){.workshops-layout{grid-template-columns:1fr}}
        .filters-title{font-family:var(--font-mono);font-size:0.7rem;letter-spacing:0.06em;text-transform:uppercase;color:var(--text-dim);margin:1.1rem 0 0.6rem}.filters-title:first-child{margin-top:0}
        .filter-check{display:flex;align-items:center;gap:0.55rem;font-size:0.88rem;padding:0.3rem 0;color:var(--text-muted);cursor:pointer}.filter-check input{accent-color:var(--green)}
        .wcard{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1rem;display:flex;gap:1rem;margin-bottom:1rem;align-items:stretch}.wcard-photo{width:130px;min-width:130px;border-radius:0.65rem;background:linear-gradient(135deg,var(--green-bg),var(--bg-elevated));display:flex;align-items:center;justify-content:center;font-size:2rem;border:1px solid var(--border)}.wcard-body{flex:1;display:flex;flex-direction:column}.wcard-top{display:flex;justify-content:space-between;align-items:flex-start;gap:0.5rem}.wcard-top h3{margin:0 0 0.25rem;font-size:1.05rem}.wcard-price{text-align:right;color:var(--green);font-weight:700}.wcard-price small{display:block;font-weight:400;color:var(--text-dim);font-size:0.68rem}.wcard-rating{color:var(--yellow);font-size:0.85rem;margin:0.2rem 0 0.5rem}.wcard-rating span{color:var(--text-dim);margin-left:0.3rem}.wcard-meta{color:var(--text-dim);font-size:0.82rem;margin-bottom:0.6rem}.wcard-tags{display:flex;gap:0.4rem;flex-wrap:wrap;margin-bottom:0.8rem}.tag{font-size:0.72rem;padding:0.2rem 0.6rem;border-radius:999px;border:1px solid var(--border);color:var(--text-muted)}.tag.tag-green{border-color:var(--purple);background:rgba(139,92,246,0.12);color:#c4b5fd}.wcard-actions{margin-top:auto;display:flex;gap:0.6rem}.geo-btn{background:transparent;border:1px solid var(--green-dark);color:var(--green);border-radius:0.55rem;padding:0.55rem 0.9rem;font-size:0.82rem;margin-bottom:1.25rem;cursor:pointer}.geo-btn:hover{background:var(--green-bg)}.found-count{font-family:var(--font-mono);font-size:0.72rem;letter-spacing:0.06em;text-transform:uppercase;color:var(--green);display:block;margin-bottom:0.4rem}
        .card-list{display:flex;flex-direction:column;gap:0.75rem}.card-row{background:var(--bg-card);border:1px solid var(--border);border-radius:0.85rem;padding:1rem;display:flex;align-items:center;gap:1rem;text-decoration:none;color:inherit}.card-row:hover{border-color:var(--green-dark);text-decoration:none}.card-row img{width:60px;height:60px;object-fit:cover;border-radius:0.6rem;border:1px solid var(--border)}.badge{font-family:var(--font-mono);font-size:0.68rem;padding:0.25rem 0.65rem;border-radius:999px;background:var(--bg-elevated);border:1px solid var(--border);color:var(--text-dim);text-transform:uppercase}.empty-state{text-align:center;color:var(--text-dim);padding:3rem 0}.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}.page-header h1{font-family:var(--font-serif);font-size:1.6rem;margin:0}
        .modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.65);display:flex;align-items:center;justify-content:center;z-index:1000}.modal-box{background:var(--bg-elevated);border:1px solid var(--border);border-radius:16px;padding:24px;width:92%;max-width:520px;max-height:90vh;overflow-y:auto;color:var(--text)}.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}.modal-header h2{margin:0;font-size:1.3rem}.modal-close{background:none;border:none;color:#aaa;font-size:20px;cursor:pointer}.modal-field{display:flex;flex-direction:column;gap:.35rem;margin-bottom:1rem;font-size:.85rem;font-weight:600;color:var(--text-muted)}.modal-field input{padding:.6rem .8rem;border:1px solid var(--border);border-radius:.6rem;background:var(--bg);color:var(--text);font-family:inherit}.modal-chips{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem}.modal-chips label{cursor:pointer;display:flex;align-items:center;gap:.35rem}.modal-error{color:#f87171;margin-bottom:1rem;font-size:0.85rem}.modal-actions{display:flex;gap:.75rem}.modal-actions button{flex:1}.btn-save{border:none;border-radius:.6rem;cursor:pointer;font-weight:700;background:var(--accent-gradient);color:#fff;padding:.6rem 1rem}.btn-save:disabled{opacity:.5;cursor:not-allowed}
    </style>
    @stack('styles')
</head>
<body>
<nav class="navbar">
    <a href="{{ route('reparations.create') }}" class="brand">&#129525; TextileCycle</a>
    <nav>
        <a href="{{ route('reparations.create') }}" class="{{ request()->routeIs('reparations.create') ? 'active' : '' }}">Nouvelle analyse</a>
        <a href="{{ route('reparations.index') }}" class="{{ request()->routeIs('reparations.index') ? 'active' : '' }}">Mes demandes</a>
    </nav>
</nav>
@yield('content')
<!-- Modal Ajouter un atelier -->
<div id="workshopModal" class="modal-backdrop" style="display:none" onclick="if(event.target===this)closeWorkshopModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Ajouter un atelier</h2>
            <button type="button" class="modal-close" onclick="closeWorkshopModal()">&#10005;</button>
        </div>
        <div id="modalError" class="modal-error" style="display:none"></div>
        <label class="modal-field">Nom de l'atelier *<input type="text" id="wName" placeholder="Ex: Atelier Couture Paris"/></label>
        <label class="modal-field">Adresse *<input type="text" id="wAddress" placeholder="12 rue de la Paix"/></label>
        <label class="modal-field">Ville<input type="text" id="wCity" placeholder="Paris"/></label>
        <label class="modal-field">Telephone<input type="text" id="wPhone" placeholder="+33 1 23 45 67 89"/></label>
        <p class="modal-field" style="margin-bottom:.5rem">Specialites</p>
        <div class="modal-chips" id="specialtyChips"></div>
        <div class="modal-actions">
            <button type="button" class="btn-outline" onclick="closeWorkshopModal()">Annuler</button>
            <button type="button" class="btn-save" id="btnSaveWorkshop" onclick="saveWorkshop()">Ajouter l'atelier</button>
        </div>
    </div>
</div>
<script>
const API_BASE='/api';
const SPECIALTIES=['Trou','Dechirure','Tache','Fermeture cassee','Bouton manquant','Usure','Couture','Cuir','Nettoyage specialise'];
let selSpecs=[];
function openWorkshopModal(){
    document.getElementById('wName').value='';document.getElementById('wAddress').value='';
    document.getElementById('wCity').value='';document.getElementById('wPhone').value='';
    selSpecs=[];document.getElementById('modalError').style.display='none';
    document.getElementById('specialtyChips').innerHTML=SPECIALTIES.map(s=>`<label class="tag" onclick="toggleSpec('${s}',this)"><input type="checkbox" style="accent-color:var(--green)"/> ${s}</label>`).join('');
    document.getElementById('workshopModal').style.display='flex';
}
function toggleSpec(s,el){selSpecs.includes(s)?(selSpecs=selSpecs.filter(x=>x!==s),el.classList.remove('tag-green')):(selSpecs.push(s),el.classList.add('tag-green'));}
function closeWorkshopModal(){document.getElementById('workshopModal').style.display='none';}
async function saveWorkshop(){
    const name=document.getElementById('wName').value.trim();
    const address=document.getElementById('wAddress').value.trim();
    const errEl=document.getElementById('modalError');
    const btn=document.getElementById('btnSaveWorkshop');
    if(!name||!address){errEl.textContent="Le nom et l'adresse sont obligatoires.";errEl.style.display='block';return;}
    btn.disabled=true;btn.textContent='Creation...';errEl.style.display='none';
    try{
        const res=await fetch(`${API_BASE}/workshops`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({name,address,city:document.getElementById('wCity').value.trim()||undefined,phone:document.getElementById('wPhone').value.trim()||undefined,specialties:selSpecs.length?selSpecs:['General']})});
        const data=await res.json();
        if(!res.ok)throw new Error(data.message||'Erreur serveur');
        closeWorkshopModal();
        if(typeof onWorkshopSaved==='function')onWorkshopSaved(data.data);
    }catch(err){errEl.textContent=err.message||"Erreur lors de la creation.";errEl.style.display='block';}
    finally{btn.disabled=false;btn.textContent="Ajouter l'atelier";}
}
</script>
@stack('scripts')
</body>
</html>
