{{-- Partial: marketplace/ai-recommendations --}}
<div class="ai-section section">
    <div class="ai-section-header">
        <span class="material-icons-round" style="color:var(--primary-light);font-size:1.5rem">auto_awesome</span>
        <div>
            <div style="font-weight:700;color:var(--text-primary)">Recommandations IA personnalisées</div>
            <div style="font-size:.8rem;color:var(--text-secondary)">Basées sur vos goûts, taille et historique d'achats</div>
        </div>
        <span class="ai-badge" style="margin-left:auto">Propulsé par l'IA</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem">
        @forelse($recommendations as $reco)
        <div class="product-card" style="display:flex;flex-direction:column">
            <a href="{{ route('marketplace.show', $reco) }}">
                <div class="product-img" style="height:120px">
                    @if($reco->image_url)
                        <img src="{{ asset('storage/'.$reco->image_url) }}" alt="{{ $reco->titre }}" style="width:100%;height:100%;object-fit:cover">
                    @else
                        <span style="font-size:2rem">✨</span>
                    @endif
                </div>
            </a>
            <div style="padding:.75rem">
                <div class="ai-score" style="margin-bottom:.35rem">✦ {{ $reco->ai_score }}% de compatibilité</div>
                <div style="font-size:.85rem;font-weight:700;color:var(--text-primary)">{{ Str::limit($reco->titre, 28) }}</div>
                <div style="font-size:.78rem;color:var(--text-muted)">{{ $reco->taille }} • {{ $reco->categorie }}</div>
                <div style="font-family:'Outfit',sans-serif;font-size:1.1rem;font-weight:800;color:var(--primary-light);margin-top:.35rem">
                    @if($reco->type == 'don') Gratuit @elseif($reco->type == 'echange') Échange @else {{ number_format($reco->prix, 2) }} DT @endif
                </div>
            </div>
        </div>
        @empty
        <div style="grid-column:1/-1;text-align:center;padding:1rem;color:var(--text-muted);font-size:.875rem">
            <span class="material-icons-round" style="display:block;font-size:2rem;margin-bottom:.5rem">psychology</span>
            L'IA analyse votre profil pour personnaliser les recommandations…
        </div>
        @endforelse
    </div>
    <div style="margin-top:.75rem;font-size:.78rem;color:var(--text-muted);display:flex;align-items:center;gap:.35rem">
        <span class="material-icons-round" style="font-size:.9rem">info</span>
        L'IA analyse : vos tailles préférées, catégories consultées, historique d'achats et échanges
    </div>
</div>
