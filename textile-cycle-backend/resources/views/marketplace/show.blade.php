@extends('layouts.app')

@section('title', $article->titre)
@section('meta_description', Str::limit($article->description, 155))
@section('breadcrumb', 'Marketplace › ' . Str::limit($article->titre, 30))

@section('styles')
<style>
.detail-grid{display:grid;grid-template-columns:1fr 380px;gap:2rem;align-items:start}
.main-image{width:100%;height:400px;object-fit:cover;border-radius:var(--radius-lg);background:linear-gradient(135deg,rgba(108,99,255,.1),rgba(67,217,173,.05));display:flex;align-items:center;justify-content:center;font-size:6rem;border:1px solid var(--border)}
.rating-stars{display:flex;align-items:center;gap:.25rem;font-size:1.25rem}
.star-filled{color:#FFD700} .star-empty{color:var(--border)}
.status-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .875rem;border-radius:100px;font-size:.8rem;font-weight:700}
.similar-item{display:flex;align-items:center;gap:.75rem;padding:.75rem;border-radius:var(--radius-md);background:rgba(255,255,255,.02);border:1px solid var(--border);cursor:pointer;transition:var(--transition)}
.similar-item:hover{background:rgba(108,99,255,.05);border-color:var(--border-active)}
@media(max-width:1000px){.detail-grid{grid-template-columns:1fr}}
</style>
@endsection

@section('content')
<div class="animate-fade-in-up">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
        <a href="{{ route('marketplace.index') }}" class="btn btn-secondary btn-sm">
            <span class="material-icons-round" style="font-size:1rem">arrow_back</span> Retour au marketplace
        </a>
        <div style="display:flex;gap:.5rem">
            <a href="{{ route('marketplace.edit', $article) }}" class="btn btn-secondary btn-sm" id="btn-edit-article">
                <span class="material-icons-round" style="font-size:1rem">edit</span> Modifier cet article
            </a>
            <a href="{{ route('marketplace.mes-articles') }}" class="btn btn-secondary btn-sm">
                <span class="material-icons-round" style="font-size:1rem">inventory_2</span> Mes Articles
            </a>
        </div>
    </div>

    <div class="detail-grid">
        {{-- ─── LEFT: Image + Details ─── --}}
        <div>
            {{-- Image --}}
            <div class="main-image">
                @if($article->image_url)
                    <img src="{{ asset('storage/'.$article->image_url) }}" alt="{{ $article->titre }}" style="width:100%;height:100%;object-fit:cover;border-radius:var(--radius-lg)">
                @else
                    <span>{{ ['👕','👗','👖','🧥','👟'][0] }}</span>
                @endif
            </div>

            {{-- IA Analysis Card --}}
            <div class="card section" style="margin-top:1rem;background:linear-gradient(135deg,rgba(108,99,255,.08),rgba(255,101,132,.05));border-color:rgba(108,99,255,.2)">
                <div class="card-header">
                    <div class="card-title" style="color:var(--primary-light)">
                        <span class="material-icons-round" style="font-size:1.1rem">auto_awesome</span>
                        Analyse IA automatique
                    </div>
                    <span class="ai-badge">IA</span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem">
                    <div style="background:rgba(255,255,255,.03);border-radius:var(--radius-md);padding:.875rem">
                        <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.35rem">Classification détectée</div>
                        <div style="font-weight:700;color:var(--text-primary)">{{ $article->categorie }} / {{ $article->genre }}</div>
                    </div>
                    <div style="background:rgba(255,255,255,.03);border-radius:var(--radius-md);padding:.875rem">
                        <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.35rem">Score de compatibilité</div>
                        <div style="font-weight:700;color:var(--primary-light)">{{ $article->ai_score ?? 87 }}% <span style="font-size:.75rem;color:var(--text-muted)">avec votre profil</span></div>
                    </div>
                    <div style="background:rgba(255,255,255,.03);border-radius:var(--radius-md);padding:.875rem">
                        <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.35rem">Prix estimé par l'IA</div>
                        <div style="font-weight:700;color:var(--secondary)">{{ $article->ai_prix_min ?? 20 }} – {{ $article->ai_prix_max ?? 40 }} DT</div>
                    </div>
                    <div style="background:rgba(255,255,255,.03);border-radius:var(--radius-md);padding:.875rem">
                        <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.35rem">Annonces similaires</div>
                        <div style="font-weight:700;color:var(--accent-orange)">{{ $similar->count() }} trouvées</div>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div class="card section">
                <div class="card-header">
                    <div class="card-title">Description complète</div>
                </div>
                <p style="color:var(--text-secondary);line-height:1.7">{{ $article->description }}</p>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-top:1.25rem">
                    @foreach(['Catégorie' => $article->categorie, 'Taille' => $article->taille, 'Genre' => $article->genre, 'Marque' => $article->marque ?? 'Non précisée', 'État' => $article->etat, 'Publié le' => $article->created_at->format('d/m/Y')] as $k => $v)
                        <div style="background:rgba(255,255,255,.02);border-radius:var(--radius-sm);padding:.75rem">
                            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.25rem">{{ $k }}</div>
                            <div style="font-weight:600;color:var(--text-primary);font-size:.85rem">{{ $v }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Annonces similaires (IA) --}}
            @if($similar->count() > 0)
            <div class="card section">
                <div class="card-header">
                    <div class="card-title">
                        <span class="material-icons-round" style="color:var(--primary);font-size:1.1rem">content_copy</span>
                        Articles similaires détectés par l'IA
                    </div>
                    <span class="ai-badge">IA</span>
                </div>
                <div style="display:flex;flex-direction:column;gap:.5rem">
                    @foreach($similar->take(4) as $sim)
                        <a href="{{ route('marketplace.show', $sim) }}" class="similar-item">
                            <div style="width:44px;height:44px;border-radius:var(--radius-sm);background:rgba(108,99,255,.1);display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0">👕</div>
                            <div style="flex:1;min-width:0">
                                <div style="font-weight:600;color:var(--text-primary);font-size:.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $sim->titre }}</div>
                                <div style="font-size:.78rem;color:var(--text-muted)">{{ $sim->taille }} • {{ $sim->etat }}</div>
                            </div>
                            <div style="font-family:'Outfit',sans-serif;font-weight:800;color:var(--primary-light);font-size:.9rem;flex-shrink:0">
                                {{ $sim->type == 'echange' ? 'Échange' : number_format($sim->prix ?? 15, 2).' DT' }}
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- ─── RIGHT: Actions Panel ─── --}}
        <div>
            <div class="card" style="position:sticky;top:90px">
                {{-- Price + Type --}}
                <div style="margin-bottom:1.25rem">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
                        <span class="badge {{ $article->type == 'vente' ? 'badge-primary' : 'badge-warning' }}">
                            {{ $article->type == 'vente' ? '💰 Vente' : '🔄 Échange' }}
                        </span>
                        <span class="badge {{ str_contains($article->etat, 'Neuf') || str_contains($article->etat, 'Très') ? 'badge-success' : 'badge-info' }}">
                            {{ $article->etat }}
                        </span>
                    </div>
                    <div style="font-family:'Outfit',sans-serif;font-size:2.5rem;font-weight:800;{{ $article->type == 'echange' ? 'color:var(--accent-orange)' : 'background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent' }}">
                        @if($article->type == 'echange') Échange
                        @else {{ number_format($article->prix ?? 15, 2) }} DT
                        @endif
                    </div>
                    @if($article->type == 'echange' && $article->article_echange)
                        <div style="font-size:.85rem;color:var(--text-muted);margin-top:.25rem">
                            Souhaite : {{ $article->article_echange }}
                        </div>
                    @endif
                </div>

                {{-- Seller Info --}}
                <div style="display:flex;align-items:center;gap:.875rem;padding:.875rem;background:rgba(255,255,255,.03);border-radius:var(--radius-md);margin-bottom:1.25rem">
                    <div style="width:44px;height:44px;border-radius:50%;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center">
                        <span class="material-icons-round" style="color:white;font-size:1.25rem">person</span>
                    </div>
                    <div style="flex:1">
                        <div style="font-weight:700;color:var(--text-primary)">{{ $article->vendeur->name ?? 'Anonyme' }}</div>
                        <div class="rating-stars" style="font-size:.9rem">
                            @for($i=1;$i<=5;$i++)
                                <span class="{{ $i <= intval($article->note_vendeur ?? 4) ? 'star-filled' : 'star-empty' }}">★</span>
                            @endfor
                            <span style="font-size:.78rem;color:var(--text-muted);margin-left:.25rem">{{ $article->note_vendeur ?? 4.2 }}/5</span>
                        </div>
                    </div>
                    <span class="badge badge-success" style="font-size:.7rem">Vérifié ✓</span>
                </div>

                {{-- CTA Buttons --}}
                @if($article->statut === 'disponible')
                    <form method="POST" action="{{ route('marketplace.demande', $article) }}" style="margin-bottom:.75rem">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:.875rem" id="btn-buy-request">
                            <span class="material-icons-round">{{ $article->type == 'echange' ? 'swap_horiz' : 'shopping_cart' }}</span>
                            {{ $article->type == 'echange' ? 'Proposer un échange' : 'Faire une demande d\'achat' }}
                        </button>
                    </form>
                @else
                    <div class="alert alert-warning" style="margin-bottom:.75rem">
                        <span class="material-icons-round">info</span>
                        Cet article n'est plus disponible
                    </div>
                @endif

                <form method="POST" action="{{ route('marketplace.toggle-favori', $article) }}" style="margin-bottom:.75rem">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center" id="btn-favorite">
                        <span class="material-icons-round">{{ $article->is_favori ? 'favorite' : 'favorite_border' }}</span>
                        {{ $article->is_favori ? 'Retirer des favoris' : 'Ajouter aux favoris' }}
                    </button>
                </form>

                {{-- Statut suivi --}}
                <div style="border-top:1px solid var(--border);padding-top:1rem;margin-top:.5rem">
                    <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Suivi de transaction</div>
                    @foreach(['Annonce publiée' => true, 'Demande reçue' => in_array($article->statut, ['en_cours','vendu']), 'Transaction en cours' => in_array($article->statut, ['en_cours','vendu']), 'Vente finalisée' => $article->statut === 'vendu'] as $step => $done)
                        <div style="display:flex;align-items:center;gap:.6rem;padding:.4rem 0">
                            <span class="material-icons-round" style="font-size:1rem;color:{{ $done ? 'var(--secondary)' : 'var(--text-muted)' }}">{{ $done ? 'check_circle' : 'radio_button_unchecked' }}</span>
                            <span style="font-size:.82rem;color:{{ $done ? 'var(--text-primary)' : 'var(--text-muted)' }}">{{ $step }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Évaluation --}}
                @if($article->statut === 'vendu')
                    <div style="border-top:1px solid var(--border);padding-top:1rem;margin-top:.5rem">
                        <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem;font-weight:600">Évaluer le vendeur</div>
                        <form method="POST" action="{{ route('marketplace.evaluer', $article) }}">
                            @csrf
                            <div style="display:flex;gap:.35rem;margin-bottom:.75rem" id="star-rating">
                                @for($i=1;$i<=5;$i++)
                                    <label style="cursor:pointer;font-size:1.5rem;color:#FFD700">
                                        <input type="radio" name="note" value="{{ $i }}" style="display:none">★
                                    </label>
                                @endfor
                            </div>
                            <textarea name="commentaire" class="form-control" placeholder="Votre avis sur le vendeur…" style="min-height:70px"></textarea>
                            <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;margin-top:.75rem">
                                <span class="material-icons-round">star</span> Évaluer
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
