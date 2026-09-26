"""Router rapport complet - TexTileCycle AI"""
from fastapi import APIRouter
from pydantic import BaseModel
from typing import List, Dict, Any
from datetime import datetime
import numpy as np

router = APIRouter()

class RequestRapport(BaseModel):
    periode: Dict[str, str]
    totaux: Dict[str, int]
    evolution: List[Dict[str, Any]]
    categories: List[Dict[str, Any]]

@router.post("/complet")
async def generer_rapport_complet(donnees: RequestRapport):
    total_actions = sum(donnees.totaux.values())
    evolution = donnees.evolution
    
    # Calcul de la croissance
    if len(evolution) >= 2:
        debut = evolution[0]
        fin = evolution[-1]
        croissance_depots = (fin.get('depots', 0) - debut.get('depots', 0)) / max(debut.get('depots', 1), 1) * 100
    else:
        croissance_depots = 0
    
    # Impact CO2
    co2_evite = donnees.totaux.get('depots', 0) * 0.625
    eau_economisee = donnees.totaux.get('depots', 0) * 200
    
    return {
        'success': True,
        'resume_executif': (
            f"Rapport TexTileCycle - Période du {donnees.periode.get('debut')} au {donnees.periode.get('fin')}. "
            f"Total de {total_actions} actions réalisées. "
            f"Croissance des dépôts : {croissance_depots:.1f}%. "
            f"Impact écologique : {co2_evite:.1f} kg CO2 évités et {eau_economisee:.0f}L d'eau économisée."
        ),
        'metriques_cles': {
            'total_actions': total_actions,
            'co2_evite_kg': round(co2_evite, 2),
            'eau_economisee_litres': int(eau_economisee),
            'croissance_depots_pct': round(croissance_depots, 1),
        },
        'recommandations_strategiques': [
            "Renforcer les partenariats avec les ateliers de réparation",
            "Développer la communication autour de l'impact écologique",
            "Créer un programme de fidélité pour encourager les dépôts réguliers",
            "Explorer des partenariats avec les marques textiles pour des collectes organisées",
        ],
        'genere_a': datetime.now().isoformat(),
        'source': 'TexTileCycle AI v1.0',
    }
