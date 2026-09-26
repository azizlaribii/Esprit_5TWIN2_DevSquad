"""
Router des tendances et analyses IA - TexTileCycle
"""
from fastapi import APIRouter
from pydantic import BaseModel
from typing import List, Dict, Any
import numpy as np
from scipy import stats

router = APIRouter()

class DonneeTendance(BaseModel):
    depots_par_categorie: List[Dict[str, Any]]
    evolution_mensuelle: List[Dict[str, Any]]
    taux_valorisation_mensuel: List[Dict[str, Any]]


@router.post("/tendances")
async def analyser_tendances(donnees: DonneeTendance):
    """Analyse des tendances globales avec statistiques avancées"""
    
    evolution = donnees.evolution_mensuelle
    tendances = []
    
    # Analyse des dépôts
    if len(evolution) >= 3:
        valeurs_depots = [m.get('depots', 0) for m in evolution]
        tendances.append(_analyser_serie('Dépôts', valeurs_depots, evolution, '#6C63FF'))
        
        valeurs_rep = [m.get('reparations', 0) for m in evolution]
        tendances.append(_analyser_serie('Réparations', valeurs_rep, evolution, '#FF6584'))
        
        valeurs_dons = [m.get('dons', 0) for m in evolution]
        tendances.append(_analyser_serie('Dons', valeurs_dons, evolution, '#43D9AD'))
    
    # Analyse des catégories
    categories = donnees.depots_par_categorie
    top_categories = sorted(categories, key=lambda x: x.get('count', 0), reverse=True)[:5]
    
    # Taux de valorisation
    taux = donnees.taux_valorisation_mensuel
    taux_valeurs = [t.get('taux', 0) for t in taux]
    taux_moyen = np.mean(taux_valeurs) if taux_valeurs else 0
    taux_tendance = 'hausse' if len(taux_valeurs) >= 2 and taux_valeurs[-1] > taux_valeurs[0] else 'stable'
    
    return {
        'success': True,
        'tendances': tendances,
        'top_categories': top_categories,
        'valorisation': {
            'taux_moyen': round(float(taux_moyen), 1),
            'tendance': taux_tendance,
            'evolution': taux,
        },
        'insights': _generer_insights(tendances, top_categories, taux_moyen),
    }


def _analyser_serie(nom: str, valeurs: List[float], evolution: List[dict], couleur: str) -> dict:
    """Analyse statistique d'une série temporelle"""
    if not valeurs:
        return {'metrique': nom, 'direction': 'stable', 'pourcentage': 0}
    
    arr = np.array(valeurs, dtype=float)
    n = len(arr)
    x = np.arange(n)
    
    # Régression linéaire pour la tendance
    if n >= 2:
        slope, intercept, r_value, p_value, std_err = stats.linregress(x, arr)
    else:
        slope, r_value = 0, 0
    
    # Direction
    moy_recente = np.mean(arr[-3:]) if n >= 3 else arr[-1]
    moy_ancienne = np.mean(arr[:3]) if n >= 3 else arr[0]
    
    if moy_recente > moy_ancienne * 1.05:
        direction = 'hausse'
    elif moy_recente < moy_ancienne * 0.95:
        direction = 'baisse'
    else:
        direction = 'stable'
    
    pct = ((moy_recente - moy_ancienne) / (moy_ancienne + 1e-10)) * 100
    
    # Saisonnalité détectée
    saisonnalite = _detecter_saisonnalite(arr)
    
    return {
        'metrique': nom,
        'couleur': couleur,
        'direction': direction,
        'pourcentage': round(float(pct), 1),
        'valeur_actuelle': int(arr[-1]) if n > 0 else 0,
        'moyenne': round(float(np.mean(arr)), 1),
        'max': int(np.max(arr)),
        'min': int(np.min(arr)),
        'r_carre': round(float(r_value**2), 3),
        'pente': round(float(slope), 3),
        'saisonnalite': saisonnalite,
        'valeurs': valeurs,
        'labels': [m.get('mois', '') for m in evolution],
    }


def _detecter_saisonnalite(arr: np.ndarray) -> str:
    """Détecte une saisonnalité simple dans la série"""
    if len(arr) < 4:
        return 'indéterminée'
    
    # Variance de la série
    cv = np.std(arr) / (np.mean(arr) + 1e-10)
    
    if cv > 0.4:
        return 'forte variabilité'
    elif cv > 0.2:
        return 'variabilité modérée'
    else:
        return 'stable'


def _generer_insights(tendances: list, categories: list, taux: float) -> List[str]:
    """Génère des insights textuels basés sur l'analyse"""
    insights = []
    
    for t in tendances:
        if t.get('direction') == 'hausse' and t.get('pourcentage', 0) > 10:
            insights.append(
                f"📈 Les {t['metrique'].lower()} sont en forte hausse (+{t['pourcentage']:.1f}%). "
                f"Préparez-vous à une augmentation de la demande."
            )
        elif t.get('direction') == 'baisse' and t.get('pourcentage', 0) < -10:
            insights.append(
                f"📉 Les {t['metrique'].lower()} sont en baisse ({t['pourcentage']:.1f}%). "
                f"Une campagne de sensibilisation pourrait être bénéfique."
            )
    
    if categories:
        cat_top = categories[0].get('categorie', 'N/A')
        insights.append(f"👕 La catégorie la plus populaire est '{cat_top}'. Concentrez les efforts de collecte sur cette catégorie.")
    
    if taux > 70:
        insights.append(f"🌿 Excellent taux de valorisation ({taux:.1f}%) ! La plateforme remplit pleinement sa mission circulaire.")
    elif taux < 40:
        insights.append(f"⚠️ Le taux de valorisation ({taux:.1f}%) nécessite une attention particulière. Renforcer les partenariats ateliers.")
    
    return insights
