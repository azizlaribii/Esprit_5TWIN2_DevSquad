"""
Router des prédictions IA - TexTileCycle
Utilise Prophet pour les séries temporelles et scikit-learn pour les prédictions
"""
from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from typing import List, Optional
import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import warnings
warnings.filterwarnings('ignore')

router = APIRouter()

class PointSerieTemporelle(BaseModel):
    ds: str  # date
    y: float  # valeur

class RequestPrediction(BaseModel):
    historique: List[PointSerieTemporelle]
    horizon: int = 30  # jours à prédire

class PredictionPoint(BaseModel):
    date: str
    valeur_predite: float
    intervalle_bas: float
    intervalle_haut: float

def predire_avec_prophet(historique: List[dict], horizon: int) -> dict:
    """Prédiction avec Facebook Prophet"""
    try:
        from prophet import Prophet
        
        df = pd.DataFrame(historique)
        df['ds'] = pd.to_datetime(df['ds'])
        df['y'] = df['y'].astype(float)
        
        # Configuration Prophet pour données de vêtements
        model = Prophet(
            changepoint_prior_scale=0.3,
            seasonality_mode='multiplicative',
            yearly_seasonality=True,
            weekly_seasonality=True,
            daily_seasonality=False,
        )
        
        # Saisonnalité personnalisée (soldes, saisons)
        model.add_seasonality(name='mensuelle', period=30.5, fourier_order=5)
        
        model.fit(df)
        
        future = model.make_future_dataframe(periods=horizon)
        forecast = model.predict(future)
        
        # Récupérer uniquement les prédictions futures
        future_forecast = forecast[forecast['ds'] > df['ds'].max()].tail(horizon)
        
        predictions = [
            {
                'date': row['ds'].strftime('%Y-%m-%d'),
                'valeur_predite': max(0, round(row['yhat'], 1)),
                'intervalle_bas': max(0, round(row['yhat_lower'], 1)),
                'intervalle_haut': max(0, round(row['yhat_upper'], 1)),
            }
            for _, row in future_forecast.iterrows()
        ]
        
        # Composantes de la tendance
        tendance_direction = 'stable'
        if len(df) >= 2:
            recent_mean = df['y'].tail(7).mean()
            older_mean = df['y'].head(7).mean()
            if recent_mean > older_mean * 1.05:
                tendance_direction = 'hausse'
            elif recent_mean < older_mean * 0.95:
                tendance_direction = 'baisse'
        
        return {
            'predictions': predictions,
            'tendance': tendance_direction,
            'moyenne_historique': round(df['y'].mean(), 1),
            'max_historique': int(df['y'].max()),
            'confiance': 0.85,
            'modele': 'Prophet',
            'horizon_jours': horizon,
        }
        
    except ImportError:
        return _fallback_prediction(historique, horizon)
    except Exception as e:
        return _fallback_prediction(historique, horizon)


def _fallback_prediction(historique: List[dict], horizon: int) -> dict:
    """Prédiction par régression linéaire simple (fallback)"""
    if not historique:
        return {'predictions': [], 'confiance': 0, 'modele': 'none'}
    
    y_values = [float(p['y']) for p in historique]
    x_values = list(range(len(y_values)))
    
    # Régression linéaire simple
    n = len(y_values)
    if n < 2:
        slope = 0
        intercept = y_values[0] if y_values else 0
    else:
        sum_x = sum(x_values)
        sum_y = sum(y_values)
        sum_xy = sum(x * y for x, y in zip(x_values, y_values))
        sum_x2 = sum(x**2 for x in x_values)
        
        slope = (n * sum_xy - sum_x * sum_y) / (n * sum_x2 - sum_x**2 + 1e-10)
        intercept = (sum_y - slope * sum_x) / n
    
    moyenne = sum(y_values) / len(y_values) if y_values else 0
    std = np.std(y_values) if len(y_values) > 1 else moyenne * 0.2
    
    predictions = []
    for i in range(1, horizon + 1):
        date = (datetime.now() + timedelta(days=i)).strftime('%Y-%m-%d')
        valeur = max(0, slope * (n + i) + intercept)
        predictions.append({
            'date': date,
            'valeur_predite': round(valeur, 1),
            'intervalle_bas': max(0, round(valeur - std, 1)),
            'intervalle_haut': round(valeur + std, 1),
        })
    
    tendance = 'hausse' if slope > 0.05 else ('baisse' if slope < -0.05 else 'stable')
    
    return {
        'predictions': predictions,
        'tendance': tendance,
        'moyenne_historique': round(moyenne, 1),
        'confiance': 0.65,
        'modele': 'Régression linéaire',
        'horizon_jours': horizon,
    }


@router.post("/depots")
async def predire_depots(request: RequestPrediction):
    """Prédiction du nombre de dépôts futurs"""
    historique = [{'ds': p.ds, 'y': p.y} for p in request.historique]
    result = predire_avec_prophet(historique, request.horizon)
    return {
        'success': True,
        'type': 'depots',
        **result
    }


@router.post("/reparations")
async def predire_reparations(request: RequestPrediction):
    """Prédiction du nombre de réparations"""
    historique = [{'ds': p.ds, 'y': p.y} for p in request.historique]
    result = predire_avec_prophet(historique, request.horizon)
    return {
        'success': True,
        'type': 'reparations',
        **result
    }


@router.post("/dons")
async def predire_dons(request: RequestPrediction):
    """Prédiction du nombre de dons"""
    historique = [{'ds': p.ds, 'y': p.y} for p in request.historique]
    result = predire_avec_prophet(historique, request.horizon)
    return {
        'success': True,
        'type': 'dons',
        **result
    }
