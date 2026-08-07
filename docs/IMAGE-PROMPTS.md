# Obrazové sloty veřejného webu + AI prompty

Fotky patří do `public/images/web/` — **WebP** (ideálně i JPG fallback se stejným názvem). Dokud soubor neexistuje, web na jeho místě zobrazuje texturový placeholder; **nakopírováním souboru se správným názvem se fotka objeví bez zásahu do kódu.**

## Šablona promptu

> Photorealistic interior photograph, **{SCENE}**. Bright Scandinavian style with a warm central-European home character; walls in warm off-white (#faf9f7), natural oak floor, soft diffused daylight from a large window, accent details in muted terracotta (#c45c26) and charcoal, natural linen and cotton textures with tack-sharp fabric detail, eye-level 35mm lens, f/5.6, no people, no text, no logos, clean minimalist styling, IKEA catalogue aesthetic. Aspect ratio **{AR}**.

## Sloty

| Soubor | Rozměr (AR) | {SCENE} |
|---|---|---|
| `hero` | 2400×1350 (16:9) | a spacious living room with floor-to-ceiling sheer off-white linen curtains gently diffusing morning sunlight, a charcoal sofa with terracotta cushions, generous negative space on the left third for text overlay |
| `tile-curtain` | 800×600 (4:3) | a bright kitchen window with delicate sheer white voile curtains gathered on a rail, morning light passing through the translucent fabric |
| `tile-drape` | 800×600 (4:3) | a cozy bedroom window dressed with heavy floor-length blackout curtains in muted terracotta, neatly pleated, bed with white linen bedding partially visible |
| `tile-pillow` | 800×600 (4:3) | a close arrangement of decorative cushions in natural linen, terracotta and charcoal tones on a light sofa, sharp fabric texture detail |
| `tile-cushion` | 800×600 (4:3) | a wooden bench by a window with tailored seat cushions in warm grey and terracotta fabric, neat piping detail |
| `tile-blind` | 800×600 (4:3) | a roman shade in natural linen half-lowered on a tall window, soft folds catching daylight, minimal workspace below |
| `tile-rails` | 800×600 (4:3) | a close-up detail of an elegant matte black curtain rail with rings and a neatly pleated curtain header, blurred bright interior background |
| `tile-shades` | 800×600 (4:3) | slim horizontal blinds on a sunny window, light stripes falling across a windowsill with a small plant |
| `tile-panels` | 800×600 (4:3) | japanese sliding fabric panels in layered neutral tones dividing a bright modern living space |
| `gallery-hotel` | 1200×900 (4:3) | an elegant hotel guest room with tall windows, layered window treatment of sheer curtains and heavy drapes in warm neutral tones, upholstered headboard, bedside lamp glowing softly, evening ambience |
| `gallery-castle` | 800×600 (4:3) | a historic chateau hall window with grand floor-length drapes in deep bordeaux with subtle gold trim, tall stucco walls, parquet floor |
| `gallery-living` | 1200×900 (4:3) | a family living room with generous off-white curtains framing garden view windows, oak furniture, warm afternoon light |
| `gallery-bedroom` | 800×600 (4:3) | a serene bedroom with blackout drapes in soft grey-green, layered with sheer curtains, linen bedding |
| `gallery-office` | 800×600 (4:3) | a modern office meeting room with vertical fabric blinds in light grey, long table, daylight |
| `gallery-restaurant` | 800×600 (4:3) | a stylish restaurant interior with tall windows dressed in warm neutral drapes, wooden tables set simply |
| `og` | 1200×630 | výřez z hero + wordmark (sestavit ručně z hero fotky) |

## Existující assety

- `frantisek-mlatecka-plakat.jpg` — historický plakát (zkomprimovaná kopie `textil-na-miru-web/František Mlatečka.jpg`), sekce O nás. **Negenerovat — skutečný artefakt.**

## Tipy

- Generovat na výšku většího rozměru a ořezat, ať sedí AR přesně.
- Konzistence: stejný prompt suffix pro všechny obrázky, ideálně stejný seed/styl.
- Před nasazením zkomprimovat (WebP kvalita ~80, cíl < 200 KB na dlaždici, < 400 KB hero).
