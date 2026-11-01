from io import BytesIO

import numpy as np
import torch
from fastapi import FastAPI, File, HTTPException, UploadFile
from PIL import Image
from transformers import CLIPModel, CLIPProcessor


app = FastAPI(title="Depot AI Service (Enhanced CLIP with Custom Dossier Suggestion)")


MODEL_NAME = "openai/clip-vit-base-patch32"
model = CLIPModel.from_pretrained(MODEL_NAME)
processor = CLIPProcessor.from_pretrained(MODEL_NAME)


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
    "État correct": "a used garment with minor signs of wash wear or slight fading",
    "A réparer": "a damaged garment with a hole, tear, missing button or broken zipper",
    "Usé": "a heavily worn out, stained, faded or torn garment in poor condition",
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

# ─── 3. RECOMMANDATION DE FILIÈRE ET DE PRIX ────────────────────────────────

PRIX_BASE = {
    "T-Shirts & Tops": 15, "Jeans & Pantalons": 30, "Vestes & Manteaux": 45,
    "Robes & Jupes": 28, "Chaussures": 35, "Sportswear": 20, "Accessoires": 8,
}
FACTEUR_ETAT = {
    "Neuf avec étiquette": 1.0, "Très bon état": 0.8, "Bon état": 0.55,
    "État correct": 0.35, "A réparer": 0.25, "Usé": 0.1,
}
FILIERE_PAR_ETAT = {
    "Neuf avec étiquette": "revente", "Très bon état": "revente", "Bon état": "revente",
    "État correct": "don", "A réparer": "reparation", "Usé": "recyclage",
}


def recommander(type_, etat):
    prix = round(PRIX_BASE.get(type_, 20) * FACTEUR_ETAT.get(etat, 0.3), 2)
    filiere = FILIERE_PAR_ETAT.get(etat, "don")
    if filiere == "revente" and prix < 8:
        filiere = "don"
    return filiere, prix


# ─── 4. FONCTIONS UTILITAIRES ───────────────────────────────────────────────

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


# ─── 5. ROUTES ──────────────────────────────────────────────────────────────

@app.get("/health")
def health():
    return {"status": "ok", "service": "depot-ai-enhanced"}


@app.post("/analyze-depot")
async def analyze(file: UploadFile = File(...)):
    try:
        content = await file.read()
        image = Image.open(BytesIO(content)).convert("RGB")
    except Exception:
        raise HTTPException(status_code=400, detail="Image invalide")

    # ÉTAPE 1 : vêtement ou non ?
    is_garment, prob_garment = is_garment_check(image)
    if not is_garment:
        return {
            "is_garment": False,
            "verdict": "non_vetement",
            "message": "Cette photo ne semble pas être un vêtement.",
            "confiance": round((1.0 - prob_garment) * 100, 1),
        }

    # ÉTAPE 2 : catégorie
    categorie, conf_cat = classify_dict(image, CATEGORIES)
    if conf_cat < 0.38:
        return {
            "is_garment": False,
            "verdict": "non_vetement",
            "message": "Cette photo ne semble pas être un vêtement identifiable.",
            "confiance": round((1.0 - conf_cat) * 100, 1),
        }

    subtype, _ = classify_dict(image, SUBTYPES)
    matiere, _ = classify_dict(image, MATIERES)
    etat, _ = classify_dict(image, ETATS)

    color_cv = extract_dominant_color_cv(image)
    color_clip, _ = classify_dict(image, CLIP_COLORS)
    if color_cv in ["blanc", "noir", "gris"]:
        couleur = color_cv
    else:
        couleur = color_clip if color_clip in [color_cv, "bleu", "marron", "rouge", "vert"] else color_cv

    # ÉTAPE 3 : dossier
    DEFAULT_DOSSIERS = ["T-shirt", "Pantalon", "Chaussures", "Chaussettes"]
    is_custom_dossier = False
    nouveau_dossier = None
    sub = subtype.lower()

    if categorie == "Chaussures" or "chaussure" in sub or "basket" in sub:
        dossier, categorie = "Chaussures", "Chaussures"
    elif "chaussette" in sub:
        dossier, categorie = "Chaussettes", "Accessoires"
    elif categorie == "Sportswear" or "jogging" in sub:
        dossier, categorie = "Jogging & Sportswear", "Sportswear"
        is_custom_dossier, nouveau_dossier = True, dossier
    elif categorie == "Jeans & Pantalons" or "jean" in sub or "pantalon" in sub or "short" in sub:
        dossier, categorie = "Pantalon", "Jeans & Pantalons"
    elif "t-shirt" in sub or "polo" in sub:
        dossier, categorie = "T-shirt", "T-Shirts & Tops"
    elif categorie == "Vestes & Manteaux" or "veste" in sub or "manteau" in sub:
        dossier, categorie = "Vestes & Manteaux", "Vestes & Manteaux"
        is_custom_dossier, nouveau_dossier = True, dossier
    elif categorie == "Robes & Jupes" or "robe" in sub or "jupe" in sub:
        dossier, categorie = "Robes & Jupes", "Robes & Jupes"
        is_custom_dossier, nouveau_dossier = True, dossier
    elif "pull" in sub or "sweat" in sub:
        dossier, categorie = "Pulls & Sweats", "T-Shirts & Tops"
        is_custom_dossier, nouveau_dossier = True, dossier
    else:
        dossier = categorie
        if dossier not in DEFAULT_DOSSIERS:
            is_custom_dossier, nouveau_dossier = True, dossier

    titre = f"{subtype} {couleur}"
    if matiere and matiere != "coton" and matiere.lower() not in subtype.lower():
        titre += f" en {matiere}"

    # ÉTAPE 4 : recommandation de filière et de prix
    filiere, prix = recommander(categorie, etat)

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
        "filiere_recommandee": filiere,
        "estimation_prix": prix if filiere == "revente" else None,
    }