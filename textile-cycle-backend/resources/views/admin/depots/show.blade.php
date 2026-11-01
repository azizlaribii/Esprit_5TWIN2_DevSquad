@extends('layouts.app')

@section('title', 'Administration · Dépôt #'.$depot->id)
@section('breadcrumb', 'Administration › Dépôts › Détail')

@section('content')
<div class="animate-fade-in-up">
    <h1 class="page-title">Dépôt #{{ $depot->id }}</h1>
    <p class="page-subtitle">Examen du dépôt et de ses valorisations</p>

    @include('admin._backoffice-nav')

    {{-- ───────── Dépôt ───────── --}}
    <div class="card section">
        <div style="display:grid;grid-template-columns:{{ $depot->photo ? '1fr 1fr' : '1fr' }};gap:2rem;align-items:start">
            <div>
                <div class="grid-2" style="margin-bottom:1.5rem">
                    <div>
                        <div class="form-label">Déposant</div>
                        <div>{{ $depot->user->name ?? 'Utilisateur #'.$depot->user_id }}</div>
                    </div>
                    <div>
                        <div class="form-label">Catégorie</div>
                        <div>{{ $depot->categorie }}</div>
                    </div>
                    <div>
                        <div class="form-label">Quantité</div>
                        <div>{{ $depot->quantite }} pièces</div>
                    </div>
                    <div>
                        <div class="form-label">Date du dépôt</div>
                        <div>{{ $depot->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="form-label">État</div>
                        <span class="badge badge-info">{{ $depot->etat }}</span>
                    </div>
                    <div>
                        <div class="form-label">Statut</div>
                        <span class="badge {{ ['valide' => 'badge-success', 'refuse' => 'badge-danger', 'traite' => 'badge-info'][$depot->statut] ?? 'badge-warning' }}">
                            {{ \App\Models\Depot::STATUTS[$depot->statut] ?? $depot->statut }}
                        </span>
                    </div>
                </div>

                @if($depot->description)
                    <div class="form-label">Description</div>
                    <p style="margin-bottom:1.5rem">{{ $depot->description }}</p>
                @endif

                @if($depot->ai_type)
                    <div class="alert alert-info" style="margin-bottom:1.5rem">
                        <div>
                            <span class="ai-badge">Analyse IA</span>
                            <div style="margin-top:.5rem">
                                Type : <strong>{{ $depot->ai_type }}</strong> ·
                                Couleur : <strong>{{ $depot->ai_couleur }}</strong> ·
                                Matière : <strong>{{ $depot->ai_matiere }}</strong> ·
                                État : <strong>{{ $depot->ai_etat }}</strong> ·
                                Confiance : <strong>{{ $depot->ai_confiance }}%</strong>
                            </div>
                        </div>
                    </div>
                @endif

                @if($depot->statut === 'refuse' && $depot->motif_refus)
                    <div class="alert alert-danger" style="margin-bottom:1.5rem">
                        <div><strong>Motif du refus</strong><div>{{ $depot->motif_refus }}</div></div>
                    </div>
                @endif

                @if($depot->traite_le)
                    <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem">
                        Dernier traitement par <strong>{{ $depot->traitePar->name ?? 'un administrateur' }}</strong>
                        le {{ $depot->traite_le->format('d/m/Y à H:i') }}.
                    </p>
                @endif

                <div style="display:flex;gap:.75rem;flex-wrap:wrap">
                    <a href="{{ route('admin.depots.index') }}" class="btn btn-secondary">Retour</a>

                    @if(in_array($depot->statut, ['en_attente', 'refuse']))
                        <form method="POST" action="{{ route('admin.depots.valider', $depot) }}"
                              data-confirm
                              data-confirm-type="warning"
                              data-confirm-title="Valider le dépôt #{{ $depot->id }} ?"
                              data-confirm-message="Le dépôt sera marqué comme validé."
                              data-confirm-ok="Oui, valider">
                            @csrf
                            <button type="submit" class="btn btn-success">Valider le dépôt</button>
                        </form>
                    @endif

                    @if($depot->statut === 'valide')
                        <form method="POST" action="{{ route('admin.depots.traiter', $depot) }}"
                              data-confirm
                              data-confirm-type="warning"
                              data-confirm-title="Marquer le dépôt #{{ $depot->id }} comme traité ?"
                              data-confirm-message="Le dépôt a été collecté et valorisé."
                              data-confirm-ok="Oui, marquer traité">
                            @csrf
                            <button type="submit" class="btn btn-primary">Marquer comme traité</button>
                        </form>
                    @endif

                    @if(in_array($depot->statut, ['en_attente', 'valide']))
                        <button type="button" class="btn btn-danger js-refuser"
                                data-url="{{ route('admin.depots.refuser', $depot) }}"
                                data-title="Refuser le dépôt #{{ $depot->id }} ?">Refuser le dépôt</button>
                    @endif
                </div>
            </div>

            @if($depot->photo)
                <div>
                    <img src="{{ asset('storage/'.$depot->photo) }}"
                         style="width:100%;max-height:500px;object-fit:contain;border-radius:12px" alt="Photo du dépôt">
                </div>
            @endif
        </div>
    </div>

    {{-- ───────── Valorisations du dépôt ───────── --}}
    <div class="card section">
        <div class="card-header">
            <div class="card-title">Valorisations de ce dépôt</div>
            <span class="badge badge-primary">{{ $depot->valorisations->count() }}</span>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th># ID</th><th>Filière</th><th>Statut</th><th>Estimation</th>
                        <th>Source</th><th>Commentaire</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($depot->valorisations as $v)
                    <tr>
                        <td>#{{ $v->id }}</td>
                        <td><span class="badge badge-primary">{{ \App\Models\Valorisation::FILIERES[$v->filiere] ?? $v->filiere }}</span></td>
                        <td>
                            <span class="badge {{ ['validee' => 'badge-success', 'realisee' => 'badge-success', 'refusee' => 'badge-danger'][$v->statut] ?? 'badge-warning' }}">
                                {{ \App\Models\Valorisation::STATUTS[$v->statut] ?? $v->statut }}
                            </span>
                        </td>
                        <td>{{ $v->estimation_prix !== null ? number_format($v->estimation_prix, 2).' DT' : '-' }}</td>
                        <td>@if($v->source === 'ia') <span class="ai-badge">IA</span> @else Manuelle @endif</td>
                        <td style="max-width:260px">
                            {{ $v->commentaire }}
                            @if($v->statut === 'refusee' && $v->motif_refus)
                                <div style="color:var(--accent-red);font-size:.8rem">Refus : {{ $v->motif_refus }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:.4rem;flex-wrap:wrap">
                                @if(in_array($v->statut, ['propose', 'refusee']))
                                    <form method="POST" action="{{ route('admin.valorisations.valider', $v) }}"
                                          data-confirm
                                          data-confirm-type="warning"
                                          data-confirm-title="Valider la valorisation #{{ $v->id }} ?"
                                          data-confirm-message="Filière « {{ \App\Models\Valorisation::FILIERES[$v->filiere] ?? $v->filiere }} » pour le dépôt #{{ $depot->id }}."
                                          data-confirm-ok="Oui, valider">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Valider</button>
                                    </form>
                                @endif

                                @if($v->statut === 'validee')
                                    <form method="POST" action="{{ route('admin.valorisations.realiser', $v) }}"
                                          data-confirm
                                          data-confirm-type="warning"
                                          data-confirm-title="Marquer la valorisation #{{ $v->id }} comme réalisée ?"
                                          data-confirm-message="La filière a bien été mise en œuvre."
                                          data-confirm-ok="Oui, réalisée">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Réalisée</button>
                                    </form>
                                @endif

                                @if(in_array($v->statut, ['propose', 'validee']))
                                    <button type="button" class="btn btn-danger btn-sm js-refuser"
                                            data-url="{{ route('admin.valorisations.refuser', $v) }}"
                                            data-title="Refuser la valorisation #{{ $v->id }} ?">Refuser</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">
                            Aucune valorisation pour ce dépôt.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin._refus-modal')
@endsection
