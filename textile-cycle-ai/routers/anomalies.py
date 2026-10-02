"""
Router de détection d'anomalies - TexTileCycle
Utilise Isolation Forest pour détecter les comportements inhabituels
"""
from fastapi import APIRouter
from pydantic import BaseModel
from typing import List, Dict, Any, Optional
import numpy as np

router = APIRouter()

class DonneesAnomalies(BaseModel):
    serie_depots: List[Dict[str, Any]]
    serie_reparations: List[Dict[str, Any]]

@router.post("/anomalies")
async def detecter_anomalies(donnees: DonneesAnomalies):
    """Détecte les anomalies dans les séries temporelles"""
    anomalies = []
    
    # Analyse des dépôts
    if donnees.serie_depots:
        vals = [float(d.get('count', 0)) for d in donnees.serie_depots]
        anomalies_depots = _detecter_isolation_forest(vals, donnees.serie_depots, 'depots')
        anomalies.extend(anomalies_depots)
    
    # Analyse des réparations
    if donnees.serie_reparations:
        vals = [float(d.get('count', 0)) for d in donnees.serie_reparations]
        anomalies_rep = _detecter_isolation_forest(vals, donnees.serie_reparations, 'reparations')
        anomalies.extend(anomalies_rep)
    
    return {
        'success': True,
        'anomalies': anomalies,
        'nb_anomalies': len(anomalies),
        'statut': 'anomalies_detectees' if anomalies else 'normal',
    }


def _detecter_isolation_forest(valeurs: List[float], serie: List[dict], type_serie: str) -> list:
    """Détection d'anomalies avec Isolation Forest ou Z-score"""
    if len(valeurs) < 5:
        return []
    
    arr = np.array(valeurs)
    
    try:
        from sklearn.ensemble import IsolationForest
        X = arr.reshape(-1, 1)
        clf = IsolationForest(contamination=0.1, random_state=42)
        predictions = clf.fit_predict(X)
        anomalies_idx = np.where(predictions == -1)[0]
    except ImportError:
        # Fallback: Z-score
        mean, std = np.mean(arr), np.std(arr)
        z_scores = np.abs((arr - mean) / (std + 1e-10))
        anomalies_idx = np.where(z_scores > 2.5)[0]
    
    anomalies = []
    for idx in anomalies_idx:
        if idx < len(serie):
            point = serie[idx]
            val = valeurs[idx]
            mean = np.mean(valeurs)
            direction = 'pic' if val > mean else 'creux'
            anomalies.append({
                'date': point.get('date', ''),
                'type_serie': type_serie,
                'valeur': val,
                'valeur_attendue': round(mean, 1),
                'ecart': round(abs(val - mean), 1),
                'direction': direction,
                'severite': 'haute' if abs(val - mean) > 2 * np.std(valeurs) else 'moyenne',
            })
    
    return anomalies
