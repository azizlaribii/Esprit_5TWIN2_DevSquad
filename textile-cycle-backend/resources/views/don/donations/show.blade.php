@extends('layouts.app')

@section('title', $donation->title . ' — Détails du don')
@section('meta_description', 'Détails du don textile et recommandations d\'associations par l\'IA')
@section('breadcrumb', 'Gestion › Dons › ' . Str::limit($donation->title, 25))

@section('head')
    @if ($donation->status === \App\Models\Donation::PENDING_ANALYSIS)
        <meta http-equiv="refresh" content="5">
    @endif
@endsection

@section('styles')
<style>
    .donation-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 900px) {
        .donation-layout { grid-template-columns: 1fr; }
    }
    .photo-main {
        width: 100%;
        height: 280px;
        object-fit: cover;
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
    }
    .photo-thumb {
        width: 68px;
        height: 68px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid var(--border);
        cursor: pointer;
        transition: var(--transition);
    }
    .photo-thumb:hover, .photo-thumb.active {
        border-color: var(--primary);
        transform: scale(1.05);
    }
    .char-badge-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: .75rem;
        margin-top: 1rem;
    }
    .char-item {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .65rem .85rem;
    }
    .char-item-label {
        font-size: .7rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: .2rem;
        display: flex;
        align-items: center;
        gap: .25rem;
    }
    .char-item-val {
        font-size: .88rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .ai-card {
        background: linear-gradient(135deg, rgba(108, 99, 255, 0.08), rgba(67, 217, 173, 0.04));
        border: 1px solid rgba(108, 99, 255, 0.25);
        border-radius: var(--radius-md);
        padding: 1.25rem;
        margin-top: 1.25rem;
        position: relative;
        overflow: hidden;
    }
    .ai-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 2px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
    }
    .match-item {
        background: var(--bg-card);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.25rem;
        margin-bottom: 1rem;
        transition: var(--transition);
        position: relative;
    }
    .match-item:hover {
        border-color: var(--primary);
        box-shadow: 0 8px 24px rgba(108, 99, 255, 0.12);
    }
    .pulse-radar {
        animation: pulseRadar 2s infinite ease-in-out;
    }
    @keyframes pulseRadar {
        0% { transform: scale(0.97); opacity: 0.8; }
        50% { transform: scale(1.02); opacity: 1; }
        100% { transform: scale(0.97); opacity: 0.8; }
    }
</style>
@endsection

@section('content')
@php
    use App\Models\Donation;
    use App\Models\DonationMatch;
    use App\Support\Textile;

    $suggested = $donation->matches->where('status', DonationMatch::SUGGESTED);
    $chosen    = $donation->matches->first(fn ($m) => in_array($m->status, [
        DonationMatch::REQUESTED, DonationMatch::ACCEPTED, DonationMatch::COMPLETED,
    ], true));
    $rejected  = $donation->matches->where('status', DonationMatch::REJECTED);
    $aiFilled  = $donation->ai_metadata['filled'] ?? [];
    $aiNotes   = $donation->ai_metadata['result']['notes'] ?? null;
    $aiConf    = $donation->ai_metadata['confidence'] ?? null;

    $statusClass = match($donation->status) {
        Donation::ACCEPTED, Donation::COMPLETED => 'badge-success',
        Donation::MATCHED                      => 'badge-info',
        Donation::PENDING_ANALYSIS             => 'badge-warning',
        Donation::NEEDS_REVIEW                 => 'badge-warning',
        Donation::REQUESTED                    => 'badge-info',
        Donation::CANCELLED                    => 'badge-danger',
        default                                => 'badge-secondary',
    };
@endphp

<div class="animate-fade-in-up">

    {{-- Top Back Nav & Actions --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <a href="{{ route('dons.index') }}"
           style="color:var(--text-muted);font-size:.85rem;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:var(--transition)"
           onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
            <span class="material-icons-round" style="font-size:1.1rem">arrow_back</span>
            Retour à l'historique des dons
        </a>

        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap">
            @can('update', $donation)
                <a href="{{ route('donations.edit', $donation) }}" class="btn btn-secondary btn-sm" style="display:inline-flex;align-items:center;gap:.3rem">
                    <span class="material-icons-round" style="font-size:.95rem">edit</span> Modifier
                </a>
            @endcan

            @can('delete', $donation)
                <form method="POST" action="{{ route('donations.destroy', $donation) }}"
                      onsubmit="return confirm('Supprimer définitivement ce don ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" style="display:inline-flex;align-items:center;gap:.3rem">
                        <span class="material-icons-round" style="font-size:.95rem">delete</span> Supprimer le don
                    </button>
                </form>
            @endcan
        </div>
    </div>

    {{-- Main Header Card --}}
    <div class="card" style="margin-bottom:1.5rem">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem">
            <div>
                <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.35rem">
                    <h1 class="page-title" style="margin-bottom:0;font-size:1.5rem">{{ $donation->title }}</h1>
                    <span class="badge {{ $statusClass }}">
                        @if($donation->status === Donation::PENDING_ANALYSIS)
                            <span class="material-icons-round" style="font-size:.75rem;animation:spin 1s linear infinite">sync</span>
                        @endif
                        {{ $donation->statusLabel() }}
                    </span>
                </div>
                <p class="page-subtitle" style="margin-bottom:0">
                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle">layers</span>
                    <strong>{{ $donation->quantity }}</strong> pièce(s) &nbsp;·&nbsp;
                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle">location_on</span>
                    {{ $donation->city }} &nbsp;·&nbsp;
                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle">calendar_today</span>
                    Déposé le {{ $donation->created_at->format('d/m/Y à H:i') }}
                </p>
            </div>

            @if($donation->status === Donation::PENDING_ANALYSIS)
                <div style="background:rgba(255,167,38,.1);border:1px solid rgba(255,167,38,.3);padding:.5rem .85rem;border-radius:var(--radius-sm);font-size:.8rem;color:var(--accent-orange);display:flex;align-items:center;gap:.4rem">
                    <span class="material-icons-round" style="font-size:1rem;animation:spin 1s linear infinite">autorenew</span>
                    Actualisation auto toutes les 5s…
                </div>
            @endif
        </div>
    </div>

    {{-- Two Columns Layout --}}
    <div class="donation-layout">

        {{-- ── Left Column: Photos & Textile Characteristics ── --}}
        <div>
            {{-- Photos Card --}}
            <div class="card" style="margin-bottom:1.5rem">
                <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:.875rem;display:flex;align-items:center;gap:.4rem">
                    <span class="material-icons-round" style="color:var(--primary-light)">photo_library</span>
                    Photos du don ({{ $donation->photos->count() }})
                </h3>

                @if($donation->photos->isNotEmpty())
                    @php $firstPhoto = $donation->photos->first(); @endphp
                    <img id="main-preview-img" src="{{ $firstPhoto->url() }}" alt="{{ $donation->title }}" class="photo-main">

                    @if($donation->photos->count() > 1)
                        <div style="display:flex;gap:.5rem;margin-top:.75rem;overflow-x:auto;padding-bottom:.35rem">
                            @foreach($donation->photos as $idx => $photo)
                                <img src="{{ $photo->url() }}" alt="Photo {{ $idx + 1 }}"
                                     class="photo-thumb {{ $idx === 0 ? 'active' : '' }}"
                                     onclick="changeMainPhoto(this, '{{ $photo->url() }}')">
                            @endforeach
                        </div>
                    @endif
                @else
                    <div style="height:180px;border-radius:var(--radius-md);border:1.5px dashed var(--border);display:flex;align-items:center;justify-content:center;flex-direction:column;color:var(--text-muted)">
                        <span class="material-icons-round" style="font-size:2.5rem;margin-bottom:.35rem">image_not_supported</span>
                        <span style="font-size:.85rem">Aucune photo fournie</span>
                    </div>
                @endif
            </div>

            {{-- Characteristics Card --}}
            <div class="card" style="margin-bottom:1.5rem">
                <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:.5rem;display:flex;align-items:center;gap:.4rem">
                    <span class="material-icons-round" style="color:var(--secondary)">checkroom</span>
                    Caractéristiques textile
                </h3>

                <div class="char-badge-grid">
                    <div class="char-item">
                        <div class="char-item-label">
                            <span class="material-icons-round" style="font-size:.8rem">category</span> Catégorie
                        </div>
                        <div class="char-item-val">{{ Textile::label('categories', $donation->category) ?: '—' }}</div>
                    </div>

                    <div class="char-item">
                        <div class="char-item-label">
                            <span class="material-icons-round" style="font-size:.8rem">verified</span> État
                        </div>
                        <div class="char-item-val">{{ Textile::label('conditions', $donation->condition) ?: '—' }}</div>
                    </div>

                    <div class="char-item">
                        <div class="char-item-label">
                            <span class="material-icons-round" style="font-size:.8rem">people</span> Public
                        </div>
                        <div class="char-item-val">{{ Textile::label('age_groups', $donation->age_group) ?: '—' }}</div>
                    </div>

                    <div class="char-item">
                        <div class="char-item-label">
                            <span class="material-icons-round" style="font-size:.8rem">straighten</span> Taille
                        </div>
                        <div class="char-item-val">{{ $donation->size ?: '—' }}</div>
                    </div>

                    <div class="char-item">
                        <div class="char-item-label">
                            <span class="material-icons-round" style="font-size:.8rem">wc</span> Genre
                        </div>
                        <div class="char-item-val">{{ Textile::label('genders', $donation->gender) ?: '—' }}</div>
                    </div>

                    <div class="char-item">
                        <div class="char-item-label">
                            <span class="material-icons-round" style="font-size:.8rem">wb_sunny</span> Saison
                        </div>
                        <div class="char-item-val">{{ Textile::label('seasons', $donation->season) ?: '—' }}</div>
                    </div>
                </div>

                @if($donation->description)
                    <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border)">
                        <div style="font-size:.78rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem">Description du donateur</div>
                        <p style="font-size:.88rem;color:var(--text-secondary);line-height:1.6;margin:0">
                            {!! nl2br(e($donation->description)) !!}
                        </p>
                    </div>
                @endif

                {{-- AI Metadata Box --}}
                @if(count($aiFilled) || $aiNotes || $aiConf !== null)
                    <div class="ai-card">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;flex-wrap:wrap;gap:.5rem">
                            <div style="display:flex;align-items:center;gap:.35rem;font-size:.85rem;font-weight:700;color:var(--primary-light)">
                                <span class="material-icons-round" style="font-size:1.1rem">auto_awesome</span>
                                Détection automatique par IA
                            </div>
                            @if($aiConf !== null)
                                <span class="badge" style="background:rgba(67,217,173,.15);color:var(--secondary);font-size:.75rem">
                                    Confiance {{ round($aiConf * 100) }} %
                                </span>
                            @endif
                        </div>

                        @if(count($aiFilled))
                            <p style="font-size:.82rem;color:var(--text-secondary);margin-bottom:.5rem">
                                ✨ L'IA a déduit :
                                <strong>{{ collect($aiFilled)->map(fn ($f) => ['category' => 'la catégorie', 'age_group' => 'le public', 'size' => 'la taille', 'gender' => 'le genre', 'season' => 'la saison', 'condition' => 'l\'état'][$f] ?? $f)->join(', ', ' et ') }}</strong>.
                            </p>
                        @endif

                        @if($aiNotes)
                            <div style="font-size:.8rem;color:var(--text-muted);font-style:italic;background:rgba(0,0,0,.2);padding:.5rem .75rem;border-radius:var(--radius-sm)">
                                « {{ $aiNotes }} »
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Right Column: Status & Association Matching ── --}}
        <div>
            @switch($donation->status)

                {{-- 1. PENDING ANALYSIS --}}
                @case(Donation::PENDING_ANALYSIS)
                    <div class="card pulse-radar" style="border-color:rgba(108,99,255,.4);text-align:center;padding:2.5rem 1.5rem">
                        <div style="width:64px;height:64px;border-radius:50%;background:rgba(108,99,255,.15);color:var(--primary-light);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem">
                            <span class="material-icons-round" style="font-size:2.2rem;animation:spin 2s linear infinite">psychology</span>
                        </div>
                        <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:.5rem">
                            Analyse IA en cours…
                        </h2>
                        <p style="font-size:.88rem;color:var(--text-secondary);max-width:380px;margin:0 auto 1.5rem;line-height:1.5">
                            Notre modèle analyse vos photos pour déterminer les attributs du vêtement et calculer les meilleures associations compatibles.
                        </p>
                        <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.05);padding:.4rem 1rem;border-radius:100px;font-size:.78rem;color:var(--text-muted)">
                            <span class="material-icons-round" style="font-size:.9rem;color:var(--secondary)">refresh</span>
                            La page se met à jour automatiquement
                        </div>
                    </div>
                    @break

                {{-- 2. NEEDS REVIEW --}}
                @case(Donation::NEEDS_REVIEW)
                    <div class="card" style="border-color:rgba(255,167,38,.4);padding:2rem">
                        <div style="display:flex;align-items:flex-start;gap:1rem;margin-bottom:1.25rem">
                            <div style="width:48px;height:48px;border-radius:12px;background:rgba(255,167,38,.15);color:var(--accent-orange);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <span class="material-icons-round" style="font-size:1.6rem">assignment_late</span>
                            </div>
                            <div>
                                <h2 style="font-size:1.15rem;font-weight:700;color:var(--text-primary);margin-bottom:.3rem">
                                    Informations complémentaires nécessaires
                                </h2>
                                <p style="font-size:.85rem;color:var(--text-secondary);margin:0;line-height:1.5">
                                    L'analyse automatique n'a pas pu déterminer avec certitude la catégorie et l'état. Complétez-les pour débloquer les suggestions.
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('donations.edit', $donation) }}" class="btn btn-primary" style="width:100%;justify-content:center">
                            <span class="material-icons-round">edit</span>
                            Compléter le don maintenant
                        </a>
                    </div>
                    @break

                {{-- 3. NO MATCH --}}
                @case(Donation::NO_MATCH)
                    <div class="card" style="text-align:center;padding:2.5rem 1.5rem">
                        <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,.06);color:var(--text-muted);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem">
                            <span class="material-icons-round" style="font-size:2rem">sentiment_dissatisfied</span>
                        </div>
                        <h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary);margin-bottom:.5rem">
                            Aucune association compatible trouvée
                        </h2>
                        <p style="font-size:.85rem;color:var(--text-secondary);max-width:380px;margin:0 auto 1.5rem;line-height:1.5">
                            Aucune association proche de vous n'a de besoin immédiat correspondant à ce vêtement. De nouveaux besoins sont publiés régulièrement.
                        </p>
                        <form method="POST" action="{{ route('donations.rematch', $donation) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary" style="margin:0 auto">
                                <span class="material-icons-round">replay</span> Relancer la recherche
                            </button>
                        </form>
                    </div>
                    @break

                {{-- 4. MATCHED --}}
                @case(Donation::MATCHED)
                    <div class="card">
                        <div style="margin-bottom:1.25rem">
                            <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.25rem">
                                <span class="material-icons-round" style="color:var(--secondary);font-size:1.2rem">stars</span>
                                <h2 style="font-size:1.15rem;font-weight:700;color:var(--text-primary);margin:0">
                                    Associations recommandées par l'IA
                                </h2>
                            </div>
                            <p style="font-size:.82rem;color:var(--text-muted);margin:0">
                                Classées par taux de pertinence selon les besoins réels et la proximité.
                            </p>
                        </div>

                        @forelse ($suggested as $match)
                            <div class="match-item">
                                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:.75rem">
                                    <div>
                                        <h3 style="font-size:1.05rem;font-weight:700;color:var(--text-primary);margin:0 0 .2rem">
                                            {{ $match->association->nom ?? $match->association->name }}
                                        </h3>
                                        <div style="font-size:.78rem;color:var(--text-muted);display:flex;align-items:center;gap:.25rem">
                                            <span class="material-icons-round" style="font-size:.85rem">location_on</span>
                                            {{ $match->association->city ?: $match->association->adresse }}
                                            @if($match->association->phone)
                                                &nbsp;·&nbsp;
                                                <span class="material-icons-round" style="font-size:.85rem">phone</span>
                                                {{ $match->association->phone }}
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Score Pill --}}
                                    <div style="text-align:right;flex-shrink:0">
                                        <div style="font-size:.85rem;font-weight:800;color:var(--secondary)">
                                            {{ round($match->score) }}% match
                                        </div>
                                        <div style="width:70px;height:5px;background:rgba(255,255,255,.08);border-radius:10px;margin-top:.3rem;overflow:hidden">
                                            <div style="width:{{ min(100, max(0, $match->score)) }}%;height:100%;background:linear-gradient(90deg, var(--primary), var(--secondary));border-radius:10px"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- AI Explanation --}}
                                <div style="font-size:.82rem;color:var(--text-secondary);line-height:1.5;margin-bottom:1rem;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);padding:.6rem .8rem;border-radius:var(--radius-sm)">
                                    <span class="material-icons-round" style="font-size:.9rem;color:var(--primary-light);vertical-align:middle;margin-right:.25rem">auto_awesome</span>
                                    {{ $match->displayExplanation() }}
                                </div>

                                @if ($match->explanation && $match->reasons)
                                    <details style="margin-bottom:1rem;font-size:.78rem;color:var(--text-muted)">
                                        <summary style="cursor:pointer;color:var(--primary-light);outline:none">Détails du calcul</summary>
                                        <ul style="margin:.4rem 0 0;padding-left:1.25rem">
                                            @foreach ($match->reasons as $reason)
                                                <li>{{ $reason }}</li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @endif

                                <form method="POST" action="{{ route('matches.choose', [$donation, $match]) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:.6rem 1rem">
                                        <span class="material-icons-round" style="font-size:1.05rem">volunteer_activism</span>
                                        Proposer mon don à cette association
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.85rem">
                                Aucune association proposée pour le moment.
                            </div>
                        @endforelse
                    </div>
                    @break

                {{-- 5. REQUESTED --}}
                @case(Donation::REQUESTED)
                    <div class="card" style="border-color:rgba(41,182,246,.35);padding:1.75rem">
                        <div style="display:flex;align-items:flex-start;gap:.875rem;margin-bottom:1.25rem">
                            <div style="width:48px;height:48px;border-radius:12px;background:rgba(41,182,246,.15);color:var(--accent-blue);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <span class="material-icons-round" style="font-size:1.6rem">hourglass_top</span>
                            </div>
                            <div>
                                <h2 style="font-size:1.15rem;font-weight:700;color:var(--text-primary);margin:0 0 .3rem">
                                    Demande transmise à {{ $chosen?->association->nom ?? $chosen?->association->name }}
                                </h2>
                                <p style="font-size:.85rem;color:var(--text-secondary);margin:0;line-height:1.5">
                                    L'association a été informée de votre offre. Dès confirmation de sa part, vous recevrez les instructions pour le dépôt.
                                </p>
                            </div>
                        </div>

                        <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.875rem;font-size:.82rem;color:var(--text-muted)">
                            <div style="display:flex;align-items:center;gap:.35rem;color:var(--text-primary);font-weight:600;margin-bottom:.35rem">
                                <span class="material-icons-round" style="font-size:1rem;color:var(--accent-blue)">info</span>
                                Si l'association décline
                            </div>
                            Le système recalculera automatiquement de nouvelles suggestions pour vous permettre de choisir une autre structure.
                        </div>
                    </div>
                    @break

                {{-- 6. ACCEPTED / COMPLETED --}}
                @case(Donation::ACCEPTED)
                @case(Donation::COMPLETED)
                    @if ($chosen)
                        <div class="card" style="border-color:rgba(67,217,173,.35);padding:1.75rem">
                            <div style="display:flex;align-items:flex-start;gap:.875rem;margin-bottom:1.5rem">
                                <div style="width:52px;height:52px;border-radius:12px;background:rgba(67,217,173,.15);color:var(--secondary);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <span class="material-icons-round" style="font-size:1.8rem">
                                        {{ $donation->status === Donation::COMPLETED ? 'task_alt' : 'celebration' }}
                                    </span>
                                </div>
                                <div>
                                    <h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary);margin:0 0 .3rem">
                                        @if ($donation->status === Donation::COMPLETED)
                                            Don remis avec succès !
                                        @else
                                            {{ $chosen->association->nom ?? $chosen->association->name }} a accepté votre don !
                                        @endif
                                    </h2>
                                    <p style="font-size:.85rem;color:var(--text-secondary);margin:0;line-height:1.5">
                                        Merci pour votre solidarité et votre engagement pour l'économie circulaire textile.
                                    </p>
                                </div>
                            </div>

                            <div style="background:rgba(67,217,173,.05);border:1px solid rgba(67,217,173,.2);border-radius:var(--radius-md);padding:1.25rem">
                                <h3 style="font-size:.9rem;font-weight:700;color:var(--secondary);margin:0 0 1rem;text-transform:uppercase;letter-spacing:.5px">
                                    Modalités de remise
                                </h3>

                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.875rem;font-size:.85rem">
                                    <div>
                                        <div style="color:var(--text-muted);font-size:.75rem">Modalité</div>
                                        <div style="font-weight:600;color:var(--text-primary)">
                                            {{ Textile::MEETING_TYPES[$chosen->meeting_type] ?? 'Dépôt direct' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div style="color:var(--text-muted);font-size:.75rem">Date & Heure</div>
                                        <div style="font-weight:600;color:var(--text-primary)">
                                            {{ $chosen->meeting_at?->format('d/m/Y à H:i') ?? 'À convenir' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div style="color:var(--text-muted);font-size:.75rem">Ville</div>
                                        <div style="font-weight:600;color:var(--text-primary)">
                                            {{ $chosen->association->city ?? $chosen->association->adresse }}
                                        </div>
                                    </div>
                                    @if ($chosen->association->phone)
                                        <div>
                                            <div style="color:var(--text-muted);font-size:.75rem">Téléphone</div>
                                            <div style="font-weight:600;color:var(--text-primary)">
                                                {{ $chosen->association->phone }}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if ($chosen->meeting_note)
                                    <div style="margin-top:1rem;padding-top:.875rem;border-top:1px solid rgba(255,255,255,.06);font-size:.82rem">
                                        <strong style="color:var(--text-primary)">Consignes de l'association :</strong>
                                        <p style="margin:.3rem 0 0;color:var(--text-secondary);line-height:1.5">
                                            {{ $chosen->meeting_note }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    @break

            @endswitch

            {{-- Rejected matches list (if any) --}}
            @if ($rejected->isNotEmpty())
                <div class="card" style="margin-top:1.5rem">
                    <h4 style="font-size:.9rem;font-weight:700;color:var(--text-muted);margin:0 0 .75rem;display:flex;align-items:center;gap:.35rem">
                        <span class="material-icons-round" style="font-size:1rem;color:var(--accent-red)">block</span>
                        Associations ayant décliné l'offre
                    </h4>
                    <ul style="margin:0;padding-left:1.25rem;font-size:.82rem;color:var(--text-secondary)">
                        @foreach ($rejected as $rej)
                            <li style="margin-bottom:.35rem">{{ $rej->association->nom ?? $rej->association->name }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
function changeMainPhoto(thumb, url) {
    document.getElementById('main-preview-img').src = url;
    document.querySelectorAll('.photo-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}
</script>
<style>
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
@endsection
