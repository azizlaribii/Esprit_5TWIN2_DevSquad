{{-- À inclure APRÈS le div animate-fade-in-up (un parent animé casserait position:fixed) --}}
<div id="refus-modal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.65);align-items:center;justify-content:center;padding:1rem">
    <div class="card" style="max-width:480px;width:100%;transform:none">
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem">
            <span class="material-icons-round" style="font-size:2rem;color:var(--accent-red)">block</span>
            <h3 id="refus-title" style="margin:0">Refuser</h3>
        </div>

        <form id="refus-form" method="POST" action="">
            @csrf
            <div class="form-group">
                <label class="form-label">Motif du refus *</label>
                <textarea id="refus-motif" name="motif_refus" class="form-control" required minlength="5" maxlength="500"
                          placeholder="Expliquez pourquoi (visible par l'utilisateur)..."></textarea>
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end">
                <button type="button" id="refus-cancel" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-danger">Confirmer le refus</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('refus-modal');
    const form  = document.getElementById('refus-form');
    const title = document.getElementById('refus-title');
    const motif = document.getElementById('refus-motif');

    function fermer() { modal.style.display = 'none'; }

    // Tout bouton .js-refuser ouvre le pop-up avec sa propre URL
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-refuser');
        if (!btn) return;
        form.action = btn.dataset.url;
        title.textContent = btn.dataset.title || 'Refuser';
        motif.value = '';
        modal.style.display = 'flex';
        motif.focus();
    });

    document.getElementById('refus-cancel').addEventListener('click', fermer);
    modal.addEventListener('click', function (e) { if (e.target === modal) fermer(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') fermer(); });
})();
</script>
