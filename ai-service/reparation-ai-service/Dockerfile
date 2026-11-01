# Service IA "reparation" (Flask + CLIP) - port 5000
FROM python:3.12-slim

ENV PYTHONUNBUFFERED=1 \
    PIP_NO_CACHE_DIR=1 \
    HF_HOME=/opt/hf-cache

WORKDIR /app

RUN pip install torch torchvision --index-url https://download.pytorch.org/whl/cpu

COPY requirements.txt .
RUN pip install -r requirements.txt

RUN python -c "from transformers import CLIPModel, CLIPProcessor; n='openai/clip-vit-base-patch32'; CLIPModel.from_pretrained(n); CLIPProcessor.from_pretrained(n)"

ENV HF_HUB_OFFLINE=1

COPY app.py .

EXPOSE 5000

CMD ["flask", "--app", "app", "run", "--host", "0.0.0.0", "--port", "5000"]
