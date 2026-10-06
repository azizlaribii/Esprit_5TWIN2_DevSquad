"""
Configuration du service IA TexTileCycle
"""
import os
from pydantic_settings import BaseSettings

class Settings(BaseSettings):
    AI_SERVICE_SECRET: str = "textilecycle_secret_key_2026"
    FRONTEND_URL: str = "http://localhost:4200"
    DATABASE_URL: str = "mysql+pymysql://root:@localhost/textilecycle"
    MODEL_CACHE_DIR: str = "./models/cache"
    LOG_LEVEL: str = "INFO"
    
    class Config:
        env_file = ".env"

settings = Settings()
