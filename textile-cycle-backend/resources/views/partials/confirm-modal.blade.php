<div id="confirm-modal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.65);align-items:center;justify-content:center;padding:1rem">
    <div class="card" style="max-width:440px;width:100%;transform:none">
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem">
            <span id="confirm-icon" class="material-icons-round" style="font-size:2rem"></span>
            <h3 id="confirm-title" style="margin:0"></h3>
        </div>
        <p id="confirm-message" style="color:var(--text-secondary);margin-bottom:1.5rem"></p>
        <div style="display:flex;gap:.75rem;justify-content:flex-end">
            <button type="button" id="confirm-cancel" class="btn btn-secondary">Annuler</button>
            <button type="button" id="confirm-ok" class="btn btn-primary">Confirmer</button>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('confirm-modal');
    let pendingForm = null;

    function open(form) {
        pendingForm = form;
        const danger = form.dataset.confirmType === 'danger';

        document.getElementById('confirm-title').textContent = form.dataset.confirmTitle || 'Confirmation';
        document.getElementById('confirm-message').textContent = form.dataset.confirmMessage || 'Êtes-vous sûr ?';

        const icon = document.getElementById('confirm-icon');
        icon.textContent = danger ? 'delete_forever' : 'edit';
        icon.style.color = danger ? 'var(--accent-red)' : 'var(--accent-orange)';

        const ok = document.getElementById('confirm-ok');
        ok.textContent = form.dataset.confirmOk || 'Confirmer';
        ok.className = 'btn ' + (danger ? 'btn-danger' : 'btn-primary');

        modal.style.display = 'flex';
    }

    function close() {
        modal.style.display = 'none';
        pendingForm = null;
    }

    // Intercepte tout formulaire marqué data-confirm
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.hasAttribute('data-confirm') && !form.dataset.confirmed) {
            e.preventDefault();
            open(form);
        }
    });

    document.getElementById('confirm-ok').addEventListener('click', function () {
        if (pendingForm) {
            pendingForm.dataset.confirmed = '1';
            pendingForm.submit();
        }
    });
    document.getElementById('confirm-cancel').addEventListener('click', close);
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
})();
</script>