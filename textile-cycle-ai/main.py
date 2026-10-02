#!/usr/bin/env python3
"""
TexTileCycle AI Service - Service d'Intelligence Artificielle
FastAPI microservice pour les prédictions, tendances et recommandations
"""

from fastapi import FastAPI, HTTPException, Header, Depends
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
import uvicorn
import os
from dotenv import load_dotenv

# Import des routers
from routers import predictions, tendances, recommandations, anomalies, categories, rapport
from core.config import settings

load_dotenv()

app = FastAPI(
    title="TexTileCycle AI Service",
    description="Service d'intelligence artificielle pour la plateforme TexTileCycle",
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc",
)

# CORS Configuration
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://localhost:4200",  # Angular dev
        "http://localhost:8000",  # Laravel
        settings.FRONTEND_URL,
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Authentification simple par secret
async def verify_secret(x_ai_secret: str = Header(...)):
    if x_ai_secret != settings.AI_SERVICE_SECRET:
        raise HTTPException(status_code=401, detail="Secret invalide")
    return x_ai_secret

# Inclusion des routers
app.include_router(
    predictions.router,
    prefix="/predict",
    tags=["Prédictions"],
    dependencies=[Depends(verify_secret)],
)
app.include_router(
    tendances.router,
    prefix="/analyse",
    tags=["Tendances"],
    dependencies=[Depends(verify_secret)],
)
app.include_router(
    recommandations.router,
    prefix="",
    tags=["Recommandations"],
    dependencies=[Depends(verify_secret)],
)
app.include_router(
    anomalies.router,
    prefix="",
    tags=["Anomalies"],
    dependencies=[Depends(verify_secret)],
)
app.include_router(
    categories.router,
    prefix="/categories",
    tags=["Catégories"],
    dependencies=[Depends(verify_secret)],
)
app.include_router(
    rapport.router,
    prefix="/rapport",
    tags=["Rapport"],
    dependencies=[Depends(verify_secret)],
)

@app.get("/", tags=["Health"])
async def root():
    return {
        "service": "TexTileCycle AI Service",
        "version": "1.0.0",
        "status": "running",
        "models_disponibles": [
            "Prophet (séries temporelles)",
            "Isolation Forest (anomalies)",
            "Random Forest (catégories)",
            "Linear Regression (tendances)",
        ]
    }

@app.get("/health", tags=["Health"])
async def health():
    return {"status": "healthy", "service": "ai"}

if __name__ == "__main__":
    uvicorn.run(
        "main:app",
        host="0.0.0.0",
        port=int(os.getenv("PORT", 8001)),
        reload=True,
        log_level="info",
    )
