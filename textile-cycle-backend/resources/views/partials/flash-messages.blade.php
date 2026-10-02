{{-- Partial: flash-messages with auto-dismiss after 6.5s --}}
<style>
.flash-alert {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .95rem 1.25rem;
    margin-bottom: 1.25rem;
    border-radius: var(--radius-md);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
    transition: opacity .4s ease, transform .4s ease, max-height .4s ease, margin .4s ease, padding .4s ease;
    max-height: 200px;
    animation: alertSlideDown .35s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes alertSlideDown {
    from {
        opacity: 0;
        transform: translateY(-16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.flash-alert-content {
    display: flex;
    align-items: center;
    gap: .75rem;
    flex: 1;
    font-size: .9rem;
    font-weight: 500;
}
.flash-alert-close {
    background: none;
    border: none;
    color: currentColor;
    opacity: .6;
    cursor: pointer;
    padding: .25rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-sm);
    transition: opacity .2s, transform .2s;
    line-height: 1;
}
.flash-alert-close:hover {
    opacity: 1;
    transform: scale(1.15);
}
.flash-alert-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    width: 100%;
    background: currentColor;
    opacity: .4;
    transform-origin: left;
    animation: flashCountdown 6.5s linear forwards;
}
.flash-alert:hover .flash-alert-progress {
    animation-play-state: paused;
}
@keyframes flashCountdown {
    from { transform: scaleX(1); }
    to { transform: scaleX(0); }
}
.flash-alert.hiding {
    opacity: 0 !important;
    transform: translateY(-12px) scale(.98) !important;
    max-height: 0 !important;
    margin-bottom: 0 !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    border-width: 0 !important;
}
</style>

@if(session('success'))
    <div class="alert alert-success flash-alert" role="alert" data-auto-dismiss="6500">
        <div class="flash-alert-content">
            <span class="material-icons-round" style="font-size:1.25rem;flex-shrink:0">check_circle</span>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="flash-alert-close" onclick="dismissFlashAlert(this.closest('.flash-alert'))" title="Fermer">
            <span class="material-icons-round" style="font-size:1.1rem">close</span>
        </button>
        <div class="flash-alert-progress"></div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger flash-alert" role="alert" data-auto-dismiss="6500">
        <div class="flash-alert-content">
            <span class="material-icons-round" style="font-size:1.25rem;flex-shrink:0">error</span>
            <div>{{ session('error') }}</div>
        </div>
        <button type="button" class="flash-alert-close" onclick="dismissFlashAlert(this.closest('.flash-alert'))" title="Fermer">
            <span class="material-icons-round" style="font-size:1.1rem">close</span>
        </button>
        <div class="flash-alert-progress"></div>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info flash-alert" role="alert" data-auto-dismiss="6500">
        <div class="flash-alert-content">
            <span class="material-icons-round" style="font-size:1.25rem;flex-shrink:0">info</span>
            <div>{{ session('info') }}</div>
        </div>
        <button type="button" class="flash-alert-close" onclick="dismissFlashAlert(this.closest('.flash-alert'))" title="Fermer">
            <span class="material-icons-round" style="font-size:1.1rem">close</span>
        </button>
        <div class="flash-alert-progress"></div>
    </div>
@endif

@if(session('status'))
    <div class="alert alert-success flash-alert" role="alert" data-auto-dismiss="6500">
        <div class="flash-alert-content">
            <span class="material-icons-round" style="font-size:1.25rem;flex-shrink:0">check_circle</span>
            <div>{{ session('status') }}</div>
        </div>
        <button type="button" class="flash-alert-close" onclick="dismissFlashAlert(this.closest('.flash-alert'))" title="Fermer">
            <span class="material-icons-round" style="font-size:1.1rem">close</span>
        </button>
        <div class="flash-alert-progress"></div>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger flash-alert" role="alert" data-auto-dismiss="7000">
        <div class="flash-alert-content">
            <span class="material-icons-round" style="font-size:1.25rem;flex-shrink:0">warning</span>
            <ul style="list-style:none;padding:0;margin:0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="flash-alert-close" onclick="dismissFlashAlert(this.closest('.flash-alert'))" title="Fermer">
            <span class="material-icons-round" style="font-size:1.1rem">close</span>
        </button>
        <div class="flash-alert-progress"></div>
    </div>
@endif

<script>
function dismissFlashAlert(el) {
    if (!el || el.classList.contains('hiding')) return;
    el.classList.add('hiding');
    setTimeout(function() {
        if (el && el.parentNode) {
            el.parentNode.removeChild(el);
        }
    }, 450);
}

(function initFlashAlerts() {
    function setupAlerts() {
        document.querySelectorAll('.flash-alert[data-auto-dismiss]').forEach(function(alertEl) {
            if (alertEl.dataset.initialized) return;
            alertEl.dataset.initialized = 'true';

            const duration = parseInt(alertEl.getAttribute('data-auto-dismiss'), 10) || 6500;
            let timer = null;
            let remaining = duration;
            let startTime = Date.now();
            let isPaused = false;

            function startTimer() {
                startTime = Date.now();
                timer = setTimeout(function() {
                    dismissFlashAlert(alertEl);
                }, remaining);
            }

            function pauseTimer() {
                if (!isPaused && timer) {
                    clearTimeout(timer);
                    remaining -= (Date.now() - startTime);
                    isPaused = true;
                }
            }

            function resumeTimer() {
                if (isPaused) {
                    isPaused = false;
                    if (remaining > 0) {
                        startTimer();
                    } else {
                        dismissFlashAlert(alertEl);
                    }
                }
            }

            alertEl.addEventListener('mouseenter', pauseTimer);
            alertEl.addEventListener('mouseleave', resumeTimer);

            startTimer();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupAlerts);
    } else {
        setupAlerts();
    }
})();
</script>
