"""Router catégories - TexTileCycle AI"""
from fastapi import APIRouter
from pydantic import BaseModel
from typing import List, Dict, Any

router = APIRouter()

class RequestCategories(BaseModel):
    categories: List[Dict[str, Any]]
    horizon: int = 30

@router.post("/tendances")
async def categories_tendances(request: RequestCategories):
    cats_sorted = sorted(request.categories, key=lambda x: x.get('count', 0), reverse=True)
    total = sum(c.get('count', 0) for c in cats_sorted)
    return {
        'success': True,
        'categories': [
            {**c, 'part': round(c.get('count', 0) / max(total, 1) * 100, 1),
             'prediction_mois_prochain': int(c.get('count', 0) * 1.05),
             'tendance': 'hausse'}
            for c in cats_sorted[:10]
        ]
    }
