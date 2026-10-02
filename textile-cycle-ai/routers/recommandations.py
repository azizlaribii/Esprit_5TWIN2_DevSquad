"""
Router des recommandations IA - TexTileCycle
Génère des recommandations intelligentes basées sur les données
"""
from fastapi import APIRouter
from pydantic import BaseModel
from typing import List, Dict, Any, Optional
from datetime import datetime

router = APIRouter()

class ContexteRecommandation(BaseModel):
    depots_en_attente: int
    categories_dominantes: List[Dict[str, Any]]
    ateliers_disponibles: int
    associations_besoins: List[Dict[str, Any]]


@router.post("/recommandations")
async def generer_recommandations(contexte: ContexteRecommandation):
    """Génère des recommandations IA intelligentes"""
    
    recommandations = []
    score_sante = 100
    
    # === Règles basées sur les données ===
    
    # Dépôts en attente
    if contexte.depots_en_attente > 50:
        recommandations.append({
            'id': 'depots_critiques',
            'priorite': 'critique',
            'type': 'operationnel',
            'titre': '🚨 Volume critique de dépôts en attente',
            'message': f"{contexte.depots_en_attente} dépôts attendent d'être traités. Action immédiate requise.",
            'action_principale': 'Mobiliser tous les ateliers disponibles',
            'actions_secondaires': [
                'Envoyer des alertes aux ateliers partenaires',
                'Prioriser les dépôts les plus anciens',
                'Prévoir des sessions de tri d\'urgence',
            ],
            'impact_potentiel': 'Réduction de 60% des délais de traitement',
            'couleur': '#FF4444',
        })
        score_sante -= 30
    elif contexte.depots_en_attente > 20:
        recommandations.append({
            'id': 'depots_eleves',
            'priorite': 'haute',
            'type': 'operationnel',
            'titre': '⚠️ Dépôts en attente importants',
            'message': f"{contexte.depots_en_attente} dépôts en attente. Planifier une augmentation de la capacité.",
            'action_principale': 'Contacter 2-3 ateliers supplémentaires',
            'actions_secondaires': [
                'Analyser les catégories les plus représentées',
                'Préparer des sessions de tri',
            ],
            'impact_potentiel': 'Réduction de 40% du délai moyen',
            'couleur': '#FFA726',
        })
        score_sante -= 15
    
    # Ateliers disponibles
    if contexte.ateliers_disponibles < 3:
        recommandations.append({
            'id': 'ateliers_insuffisants',
            'priorite': 'haute',
            'type': 'partenariat',
            'titre': '🏪 Manque de partenaires ateliers',
            'message': f"Seulement {contexte.ateliers_disponibles} ateliers actifs. La capacité de réparation est limitée.",
            'action_principale': 'Lancer une campagne de recrutement d\'ateliers',
            'actions_secondaires': [
                'Contacter les ateliers de couture locaux',
                'Proposer des partenariats avec des écoles de mode',
                'Publier une annonce sur les réseaux professionnels',
            ],
            'impact_potentiel': 'Augmentation de 100% de la capacité de réparation',
            'couleur': '#FF6584',
        })
        score_sante -= 20
    
    # Associations avec besoins non couverts
    assoc_avec_besoins = [a for a in contexte.associations_besoins if a.get('besoins_count', 0) > 0]
    if assoc_avec_besoins:
        recommandations.append({
            'id': 'besoins_associations',
            'priorite': 'moyenne',
            'type': 'coordination',
            'titre': '🤝 Associations avec besoins non couverts',
            'message': f"{len(assoc_avec_besoins)} association(s) ont des besoins en articles non satisfaits.",
            'action_principale': 'Coordonner une redistribution ciblée des dons',
            'actions_secondaires': [
                'Informer les utilisateurs des besoins spécifiques',
                'Organiser une journée de dons ciblés',
                'Créer des alertes par catégorie manquante',
            ],
            'impact_potentiel': f"Couverture des besoins de {len(assoc_avec_besoins)} associations",
            'couleur': '#43D9AD',
        })
    
    # Catégories populaires → opportunités
    if contexte.categories_dominantes:
        top_cat = contexte.categories_dominantes[0]
        recommandations.append({
            'id': 'categorie_opportunite',
            'priorite': 'basse',
            'type': 'stratégique',
            'titre': f"📊 Opportunité : catégorie '{top_cat.get('categorie', 'N/A')}'",
            'message': f"La catégorie '{top_cat.get('categorie')}' représente la plus grande part des dépôts ({top_cat.get('count', 0)} articles).",
            'action_principale': 'Développer une expertise spécialisée pour cette catégorie',
            'actions_secondaires': [
                'Former des ateliers sur cette catégorie',
                'Créer des contenus éducatifs spécialisés',
                'Proposer des tutoriels de réparation DIY',
            ],
            'impact_potentiel': 'Augmentation de 30% du taux de valorisation',
            'couleur': '#6C63FF',
        })
    
    # Recommandation générale de sensibilisation
    recommandations.append({
        'id': 'sensibilisation',
        'priorite': 'basse',
        'type': 'communication',
        'titre': '📣 Campagne de sensibilisation saisonnière',
        'message': "La période actuelle est favorable pour une campagne de sensibilisation à l'économie circulaire.",
        'action_principale': 'Planifier une campagne sur les réseaux sociaux',
        'actions_secondaires': [
            'Contenu sur l\'impact écologique positif',
            'Témoignages d\'utilisateurs satisfaits',
            'Infographies sur les économies réalisées',
        ],
        'impact_potentiel': 'Augmentation de 25% des nouveaux utilisateurs',
        'couleur': '#29B6F6',
    })
    
    # Trier par priorité
    ordre_priorite = {'critique': 0, 'haute': 1, 'moyenne': 2, 'basse': 3}
    recommandations.sort(key=lambda x: ordre_priorite.get(x['priorite'], 99))
    
    return {
        'success': True,
        'recommandations': recommandations,
        'score_sante_plateforme': max(0, score_sante),
        'nb_actions_urgentes': len([r for r in recommandations if r['priorite'] in ['critique', 'haute']]),
        'generees_a': datetime.now().isoformat(),
        'modele': 'Rule-Based AI + Statistical Analysis',
    }
