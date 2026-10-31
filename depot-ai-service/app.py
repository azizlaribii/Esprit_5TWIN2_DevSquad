<<<<<<< HEAD
from io import BytesIO

=======
import os
from io import BytesIO

import numpy as np
>>>>>>> Aziz-Laribi
import torch
from fastapi import FastAPI, File, HTTPException, UploadFile
from PIL import Image
from transformers import CLIPModel, CLIPProcessor


app = FastAPI(title="Depot AI Service")
=======
app = FastAPI(title="Depot AI Service (Enhanced CLIP with Custom Dossier Suggestion)")


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
=======
# ─── 1. FILTRE BINAIRE : VÊTEMENT VS NON-VÊTEMENT (Animaux, Visages, Objets, etc.) ─

GARMENT_VALIDATION = {
    "vetement": [
        "a photo of a piece of clothing, garment, fashion wear, shoes, pants or textile apparel",
        "a shirt, t-shirt, polo, sweater, trousers, jeans, shorts, coat, jacket, dress or shoes",
        "a piece of wearable clothing, apparel or footwear",
        "a jogging suit, tracksuit, sweatpants, sport outfit or athletic wear"
    ],
    "non_vetement": [
        "a photo of a cat, kitten, feline, dog, puppy, canine, pet or domestic animal",
        "a photo of an animal, wildlife creature, bird or mammal without clothing",
        "a close-up portrait of a human face, skin, smile, selfie or person",
        "a photo of a car, automobile, vehicle, motorbike, bicycle or traffic",
        "a photo of food, meal, dish, pizza, bread, fruit, vegetable, cooking or beverage",
        "a photo of a phone, laptop, computer screen, electronic gadget or appliance",
        "a photo of a room, interior furniture, sofa, table, building, house or landscape",
        "a document, text page, book, diagram, screenshot, drawing or graphic illustration"
    ]
}

# ─── 2. CATÉGORIES TEXTILE ──────────────────────────────────────────────────

CATEGORIES = {
    "T-Shirts & Tops": [
        "a photo of a casual cotton t-shirt or short-sleeve polo shirt",
        "a polo shirt with collar, cotton t-shirt, top or tee",
        "a knitted woolen pullover, sweater, sweatshirt or turtleneck",
        "a casual summer top or short-sleeved cotton shirt"
    ],
    "Jeans & Pantalons": [
        "a photo of blue denim jeans or denim trousers with rivets and stitching",
        "casual chino pants, dress trousers or cotton slacks on a flat surface",
        "dark wash jeans or straight-leg denim pants folded"
    ],
    "Chaussures": [
        "a pair of shoes, leather shoes, boots or loafers",
        "a pair of athletic running sneakers, footwear or trainers"
    ],
    "Accessoires": [
        "a pair of socks or cotton ankle socks",
        "a fashion accessory, winter scarf, knitted hat, cap or belt",
        "undergarments or fashion accessories"
    ],
    "Vestes & Manteaux": [
        "a winter coat, trench coat, heavy jacket, parka, bomber or windbreaker",
        "a formal blazer, suit jacket, leather jacket or outerwear jacket"
    ],
    "Robes & Jupes": [
        "a woman dress, evening dress or summer dress",
        "a skirt or feminine dress"
    ],
    "Sportswear": [
        "a jogging suit, tracksuit pants or sweatpants with elastic waistband",
        "athletic gym sportswear, sport jersey, training pants or activewear",
        "a full tracksuit set, joggers, running pants or sport outfit",
        "sport leggings, yoga pants, athletic bottoms or workout clothes"
    ]
}

SUBTYPES = {
    "T-shirt": [
        "a casual cotton t-shirt with short sleeves",
        "a round neck cotton tee shirt"
    ],
    "Polo": [
        "a polo shirt with collar and buttons",
        "a short-sleeve collared polo shirt"
    ],
    "Pull": [
        "a thick knitted winter wool sweater, pullover or turtleneck",
        "a knitted woolen pullover jumper"
    ],
    "Jogging": [
        "jogging pants, sweatpants or tracksuit trousers with elastic waistband and drawstring",
        "a tracksuit bottom, jogger pants or sport training trousers in fleece or polyester",
        "athletic jogging bottoms, running pants or sport trousers with stripes"
    ],
    "Jean": [
        "blue denim jeans pants with rivets, copper buttons and denim stitching",
        "dark wash denim jeans or straight-leg rigid denim trousers"
    ],
    "Short": "casual bermuda shorts, board shorts or cotton casual shorts",
    "Pantalon": "casual chino pants, dress trousers or formal slacks in non-denim fabric",
    "Baskets": "sport athletic sneakers, running shoes or trainers",
    "Chaussures en cuir": "formal leather shoes, dress shoes or leather loafers",
    "Chaussettes": "a pair of cotton socks or ankle socks",
    "Veste": "a casual jacket, suit blazer or leather jacket",
    "Manteau": "a warm winter coat, trench or parka"
}

ETATS = {
    "Neuf avec étiquette": "a brand new garment with price tag attached, perfect store condition",
    "Très bon état": "a clean garment in excellent condition, crisp without any defect or stain",
    "Bon état": "a gently used clean garment in good wearable condition",
    "État correct": "a used garment with minor signs of wash wear or slight fading"
}

MATIERES = {
    "coton": "clothing made of soft breathable cotton fabric or jersey",
    "denim": "denim jeans fabric, twill denim cotton",
    "laine": "knitted wool sweater or warm woolen knitwear",
    "cuir": "genuine smooth leather, polished leather jacket or leather shoes",
    "synthétique": "polyester synthetic activewear fabric or nylon",
    "lin": "breathable lightweight linen fabric",
    "soie": "smooth shiny silk or satin fabric"
}

CLIP_COLORS = {
    "noir": "a black clothing item, jet black garment",
    "blanc": "a white clothing item, pure white garment",
    "gris": "a grey or charcoal gray garment",
    "bleu": "a blue or navy blue clothing item",
    "rouge": "a red or burgundy clothing item",
    "vert": "a green or olive green clothing item",
    "jaune": "a yellow or mustard clothing item",
    "beige": "a beige, tan or cream clothing item",
    "marron": "a brown or dark tan leather item",
    "rose": "a pink or light rose clothing item"
}


def is_garment_check(image: Image.Image) -> tuple[bool, float]:
    """Détermine si l'image représente un vêtement ou un élément non-textile (chat, chien, objet, etc.)."""
    all_prompts = []
    keys_map = []
    for k, prompts in GARMENT_VALIDATION.items():
        for p in prompts:
            all_prompts.append(p)
            keys_map.append(k)

    inputs = processor(text=all_prompts, images=image, return_tensors="pt", padding=True)
    with torch.no_grad():
        probs = model(**inputs).logits_per_image.softmax(dim=1)[0].numpy()

    scores = {}
    for k, pr in zip(keys_map, probs):
        scores[k] = scores.get(k, 0.0) + float(pr)

    v_score = scores.get("vetement", 0.0)
    nv_score = scores.get("non_vetement", 0.0)
    total = v_score + nv_score
    prob_garment = v_score / total if total > 0 else 0.5
    is_garment = prob_garment >= 0.52
    return is_garment, prob_garment


def extract_dominant_color_cv(img: Image.Image) -> str:
    """Analyse colorimétrique sur le cœur du vêtement (zone centrale 30%-70%)."""
    w, h = img.size
    center = img.crop((int(w * 0.30), int(h * 0.30), int(w * 0.70), int(h * 0.70))).resize((100, 100))
    arr = np.array(center, dtype=float)
    fg_pixels = arr.reshape(-1, 3)

    rgb = fg_pixels / 255.0
    r, g, b = rgb[:, 0], rgb[:, 1], rgb[:, 2]
    maxc = np.maximum(np.maximum(r, g), b)
    minc = np.minimum(np.minimum(r, g), b)
    v = maxc
    deltac = maxc - minc
    s = np.zeros_like(v)
    mask = maxc > 0
    s[mask] = deltac[mask] / maxc[mask]

    # Teinte
    h_arr = np.zeros_like(v)
    rc = (maxc - r) / (deltac + 1e-7)
    gc = (maxc - g) / (deltac + 1e-7)
    bc = (maxc - b) / (deltac + 1e-7)
    mask_r = (r == maxc) & (deltac > 0)
    mask_g = (g == maxc) & (deltac > 0)
    mask_b = (b == maxc) & (deltac > 0)
    h_arr[mask_r] = bc[mask_r] - gc[mask_r]
    h_arr[mask_g] = 2.0 + rc[mask_g] - bc[mask_g]
    h_arr[mask_b] = 4.0 + gc[mask_b] - rc[mask_b]
    h_arr = (h_arr / 6.0) % 1.0
    hue_deg = h_arr * 360.0

    mean_v = float(np.median(v))
    mean_s = float(np.median(s))
    mean_h = float(np.median(hue_deg))
    mean_r = float(np.mean(r))
    mean_g = float(np.mean(g))
    mean_b = float(np.mean(b))
    rb_ratio = mean_r / (mean_b + 1e-5)

    # 1. Noir achromatique sombre
    if mean_v < 0.38 and rb_ratio <= 1.06 and abs(mean_r - mean_b) < 0.04:
        return "noir"
    if mean_v < 0.22:
        return "noir"

    # 2. Blanc pur / très clair
    if mean_s < 0.15 and mean_v > 0.80:
        return "blanc"

    # 3. Marron foncé (cuir ou tissu marron sombre)
    if 10 <= mean_h < 48 and rb_ratio >= 1.08 and mean_v < 0.55:
        return "marron"

    # 4. Gris achromatique
    if mean_s < 0.14:
        return "gris"

    # 5. Teintes chromatiques
    if mean_h < 15 or mean_h >= 345:
        return "rouge"
    elif 15 <= mean_h < 45:
        if mean_v < 0.50:
            return "marron"
        elif mean_s < 0.35 and mean_v > 0.65:
            return "beige"
        return "orange"
    elif 45 <= mean_h < 68:
        return "jaune"
    elif 68 <= mean_h < 165:
        return "vert"
    elif 165 <= mean_h < 260:
        return "bleu"
    elif 260 <= mean_h < 320:
        return "violet"
    else:
        return "rose"


def classify_dict(image: Image.Image, options: dict) -> tuple[str, float]:
    all_prompts = []
    keys_map = []
    for k, v in options.items():
        if isinstance(v, list):
            for item in v:
                all_prompts.append(item)
                keys_map.append(k)
        else:
            all_prompts.append(v)
            keys_map.append(k)

    inputs = processor(text=all_prompts, images=image, return_tensors="pt", padding=True)
    with torch.no_grad():
        probs = model(**inputs).logits_per_image.softmax(dim=1)[0].numpy()

    scores = {}
    for k, pr in zip(keys_map, probs):
        scores[k] = scores.get(k, 0.0) + float(pr)

    best_key = max(scores, key=scores.get)
    return best_key, float(scores[best_key])
>>>>>>> Aziz-Laribi


@app.get("/health")
def health():
<<<<<<< HEAD
    return {"status": "ok"}
=======
    return {"status": "ok", "service": "depot-ai-enhanced"}



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
=======
        content = await file.read()
        image = Image.open(BytesIO(content)).convert("RGB")
    except Exception:
        raise HTTPException(status_code=400, detail="Image invalide")

    # ── ÉTAPE 1 : VÉRIFICATION VÊTEMENT OU NON-VÊTEMENT (CHAT, CHIEN, OBJET, ETC.) ──
    is_garment, prob_garment = is_garment_check(image)
    if not is_garment:
        return {
            "is_garment": False,
            "verdict": "non_vetement",
            "message": "Cette photo n'est pas une pièce de vêtement.",
            "confiance": round((1.0 - prob_garment) * 100, 1),
            "dossier_suggere": None,
            "titre_suggere": None,
            "is_custom_dossier": False,
            "nouveau_dossier": None
        }

    # ── ÉTAPE 2 : CLASSIFICATION VÊTEMENT DÉTAILLÉE ──
    categorie, conf_cat = classify_dict(image, CATEGORIES)

    if conf_cat < 0.38:
        return {
            "is_garment": False,
            "verdict": "non_vetement",
            "message": "Cette photo ne semble pas être un vêtement identifiable.",
            "confiance": round((1.0 - conf_cat) * 100, 1),
            "dossier_suggere": None,
            "titre_suggere": None,
            "is_custom_dossier": False,
            "nouveau_dossier": None
        }

    # Sous-type, matière, état
    subtype, _ = classify_dict(image, SUBTYPES)
    matiere, _ = classify_dict(image, MATIERES)
    etat, _ = classify_dict(image, ETATS)

    # Couleur
    color_cv = extract_dominant_color_cv(image)
    color_clip, _ = classify_dict(image, CLIP_COLORS)
    if color_cv in ["blanc", "noir", "gris"]:
        couleur = color_cv
    else:
        couleur = color_clip if color_clip in [color_cv, "bleu", "marron", "rouge", "vert"] else color_cv

    # ── ÉTAPE 3 : DÉTERMINATION DU DOSSIER ET DE LA CATÉGORIE ──
    # Les 4 dossiers pré-définis de base dans les boutons :
    # T-shirt, Pantalon, Chaussures, Chaussettes
    # Tout autre vêtement (Veste, Manteau, Robe, Jupe, Pull, etc.) génère un nouveau dossier suggéré !
    DEFAULT_DOSSIERS = ["T-shirt", "Pantalon", "Chaussures", "Chaussettes"]

    is_custom_dossier = False
    nouveau_dossier = None

    if categorie == "Chaussures" or "chaussure" in subtype.lower() or "basket" in subtype.lower():
        dossier = "Chaussures"
        categorie = "Chaussures"
    elif "chaussette" in subtype.lower() or (categorie == "Accessoires" and "chaussette" in subtype.lower()):
        dossier = "Chaussettes"
        categorie = "Accessoires"
    elif categorie == "Sportswear" or "jogging" in subtype.lower():
        # Jogging / tracksuit → nouveau dossier suggéré car absent des 4 boutons de base
        dossier = "Jogging & Sportswear"
        categorie = "Sportswear"
        is_custom_dossier = True
        nouveau_dossier = "Jogging & Sportswear"
    elif categorie == "Jeans & Pantalons" or "jean" in subtype.lower() or "pantalon" in subtype.lower() or "short" in subtype.lower():
        dossier = "Pantalon"
        categorie = "Jeans & Pantalons"
    elif "t-shirt" in subtype.lower() or "polo" in subtype.lower():
        dossier = "T-shirt"
        categorie = "T-Shirts & Tops"
    elif categorie == "Vestes & Manteaux" or "veste" in subtype.lower() or "manteau" in subtype.lower():
        # Une veste / manteau n'appartient pas aux 4 boutons de base -> nouveau dossier suggéré !
        dossier = "Vestes & Manteaux"
        categorie = "Vestes & Manteaux"
        is_custom_dossier = True
        nouveau_dossier = "Vestes & Manteaux"
    elif categorie == "Robes & Jupes" or "robe" in subtype.lower() or "jupe" in subtype.lower():
        dossier = "Robes & Jupes"
        categorie = "Robes & Jupes"
        is_custom_dossier = True
        nouveau_dossier = "Robes & Jupes"
    elif "pull" in subtype.lower() or "sweat" in subtype.lower():
        dossier = "Pulls & Sweats"
        categorie = "T-Shirts & Tops"
        is_custom_dossier = True
        nouveau_dossier = "Pulls & Sweats"
    else:
        # Toute autre pièce textile
        dossier = categorie
        if dossier not in DEFAULT_DOSSIERS:
            is_custom_dossier = True
            nouveau_dossier = dossier

    # Titre suggéré
    titre = f"{subtype} {couleur}"
    if matiere and matiere not in ["coton"] and matiere.lower() not in subtype.lower():
        titre += f" en {matiere}"

    # Estimation prix
    prix_est = {
        "Chaussures": 45.0,
        "Jeans & Pantalons": 35.0,
        "Vestes & Manteaux": 55.0,
        "Robes & Jupes": 35.0,
        "Accessoires": 12.0,
        "T-Shirts & Tops": 20.0,
        "Sportswear": 30.0
    }.get(categorie, 25.0)

    if etat == "Neuf avec étiquette":
        prix_est = round(prix_est * 1.3, 1)
    elif etat == "État correct":
        prix_est = round(prix_est * 0.75, 1)

    return {
        "is_garment": True,
        "verdict": "vetement",
        "type": categorie,
        "categorie": categorie,
        "dossier": dossier,
        "dossier_suggere": dossier,
        "is_custom_dossier": is_custom_dossier,
        "nouveau_dossier": nouveau_dossier,
        "couleur": couleur,
        "matiere": matiere,
        "etat": etat,
        "confiance": round(conf_cat * 100, 1),
        "titre_suggere": titre.capitalize(),
        "estimation_prix": prix_est
    }

