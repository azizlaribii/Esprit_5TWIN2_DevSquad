from io import BytesIO

import torch
from fastapi import FastAPI, File, HTTPException, UploadFile
from PIL import Image
from transformers import CLIPModel, CLIPProcessor

app = FastAPI(title="Depot AI Service")

MODEL_NAME = "openai/clip-vit-base-patch32"
model = CLIPModel.from_pretrained(MODEL_NAME)
processor = CLIPProcessor.from_pretrained(MODEL_NAME)

# libellé affiché -> phrase donnée à CLIP
TYPES = {
    "T-Shirts & Tops": "a t-shirt or top",
    "Pantalons & Jeans": "jeans or trousers",
    "Vestes & Manteaux": "a jacket or coat",
    "Robes & Jupes": "a dress or skirt",
    "Chaussures": "shoes",
    "Sportswear": "sportswear",
}
COULEURS = {
    "noir": "black", "blanc": "white", "gris": "gray", "bleu": "blue",
    "rouge": "red", "vert": "green", "jaune": "yellow", "beige": "beige", "marron": "brown",
}
MATIERES = {
    "coton": "cotton fabric", "denim": "denim fabric", "laine": "wool fabric",
    "cuir": "leather", "synthétique": "synthetic fabric",
}
# Détection d'état : phrases comparées par CLIP (zero-shot)
ETATS = {
    "Très bon état": "a clean garment in perfect condition, like new",
    "Bon état": "a garment in good condition with light wear",
    "A réparer": "a damaged garment with a hole, a tear or a stain",
    "Usé": "a worn out, faded and old garment",
}


def classify(image, options, template):
    prompts = [template.format(v) for v in options.values()]
    inputs = processor(text=prompts, images=image, return_tensors="pt", padding=True)
    with torch.no_grad():
        probs = model(**inputs).logits_per_image.softmax(dim=1)[0]
    i = int(probs.argmax())
    return list(options.keys())[i], float(probs[i])


@app.get("/health")
def health():
    return {"status": "ok"}


@app.post("/analyze-depot")
async def analyze(file: UploadFile = File(...)):
    try:
        image = Image.open(BytesIO(await file.read())).convert("RGB")
    except Exception:
        raise HTTPException(status_code=400, detail="Image invalide")

    type_, c_type = classify(image, TYPES, "a photo of {}")
    couleur, _ = classify(image, COULEURS, "a photo of a {} garment")
    matiere, _ = classify(image, MATIERES, "a close-up photo of {}")
    etat, _ = classify(image, ETATS, "{}")

    return {
        "type": type_,
        "couleur": couleur,
        "matiere": matiere,
        "etat": etat,
        "confiance": round(c_type * 100, 2),
    }