# Service IA "depot" (FastAPI + CLIP) - port 8002
FROM python:3.12-slim

ENV PYTHONUNBUFFERED=1 \
    PIP_NO_CACHE_DIR=1 \
    HF_HOME=/opt/hf-cache

WORKDIR /app

# PyTorch version CPU (beaucoup plus legere que la version GPU)
RUN pip install torch --index-url https://download.pytorch.org/whl/cpu

COPY requirements.txt .
RUN pip install -r requirements.txt numpy

# Le modele CLIP est telecharge une seule fois, pendant la construction de l'image
RUN python -c "from transformers import CLIPModel, CLIPProcessor; n='openai/clip-vit-base-patch32'; CLIPModel.from_pretrained(n); CLIPProcessor.from_pretrained(n)"

ENV HF_HUB_OFFLINE=1

COPY app.py .

EXPOSE 8002

CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "8002"]
