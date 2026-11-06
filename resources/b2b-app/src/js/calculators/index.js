import { STUHY } from '../config/defaults.js';
import { varByCode, roundPrice } from '../core/pricing.js';

function resolveRatio(stuhaKey, ratio, ratioCustom) {
  if (stuhaKey === 'custom') return parseFloat(ratioCustom) || 1;
  return parseFloat(ratio) || 1;
}

export function calculateCurtain(variables, input) {
  const width = parseFloat(input.width) || 0;
  const height = parseFloat(input.height) || 0;
  const stuhaKey = input.stuha || 'siroky70';
  const ratio = resolveRatio(stuhaKey, input.ratio, input.ratio_custom);
  const stuha = STUHY[stuhaKey] || STUHY.siroky70;

  const consumed = (width * ratio + 20) / 100;

  let price = consumed * varByCode(variables, 'PRIPRAVA');
  price += consumed * stuha.price;
  price += consumed * varByCode(variables, 'RASICI_STUHA');
  price += 2 * (height / 100) * varByCode(variables, 'LEMOVANI');
  price += varByCode(variables, 'KORDOVANI');
  price += consumed * (height / 100) * varByCode(variables, 'ZEHLENI');

  if (input.corners) price += 2 * varByCode(variables, 'OLUVKO_13G');
  if (input.bottom) {
    price += consumed * varByCode(variables, 'OLUVKO');
    price += consumed * varByCode(variables, 'VSITI_OLUVKO');
  }
  if (input.narasit) price += consumed * varByCode(variables, 'RASENI');
  if (input.snails) price += Math.ceil(consumed / 0.08) * varByCode(variables, 'SNECEK');

  // Kanýr – pole ve formuláři, v původním JS chybělo; přidáno podle OBNITKOVANI
  if (input.kanyr) {
    const kanyrHeight = parseFloat(input.kanyr_height) || 0;
    price += (width / 100) * (kanyrHeight / 100) * varByCode(variables, 'OBNITKOVANI');
  }

  return roundPrice(price);
}

export function calculateDrape(variables, input) {
  const materialWidth = parseFloat(input.material_width) || 0;
  const width = parseFloat(input.width) || 0;
  const height = parseFloat(input.height) || 0;
  const stuhaKey = input.stuha || 'siroky70';
  const ratio = resolveRatio(stuhaKey, input.ratio, input.ratio_custom);
  const stuha = STUHY[stuhaKey] || STUHY.siroky70;
  const folding = parseFloat(input.folding) || 0;

  const consumed = (width * ratio + 20) / 100;

  let price = consumed * varByCode(variables, 'PRIPRAVA');
  price += consumed * stuha.price;
  price += consumed * varByCode(variables, 'RASICI_STUHA');
  price += 2 * (height / 100) * varByCode(variables, 'LEMOVANI');
  price += varByCode(variables, 'KORDOVANI');
  price += consumed * (height / 100) * varByCode(variables, 'ZEHLENI');
  price += consumed * varByCode(variables, 'USITI_ZALOZKA');

  if (input.corners) price += 2 * varByCode(variables, 'OLUVKO_50G');
  if (input.narasit) price += consumed * varByCode(variables, 'RASENI');
  if (input.snails) price += Math.ceil(consumed / 0.08) * varByCode(variables, 'SNECEK');

  if (materialWidth > 0 && materialWidth < height + 10) {
    const joins = Math.floor((consumed * 100) / (materialWidth + 25));
    price += joins * (height / 100) * varByCode(variables, 'SESITI_DILU');
  }

  if (folding > 0) {
    price += folding * (width / 100) * varByCode(variables, 'NADSITI_ZACLONA');
  }

  if (input.kanyr) {
    const kanyrHeight = parseFloat(input.kanyr_height) || 0;
    price += (width / 100) * (kanyrHeight / 100) * varByCode(variables, 'OBNITKOVANI');
  }

  return roundPrice(price);
}

export function calculatePillow(variables, input) {
  const width = parseFloat(input.width) || 0;
  const height = parseFloat(input.height) || 0;
  const stuffing = !!input.stuffing;
  const complex = !!input.complex;

  const size = Math.ceil(width / 10) + Math.ceil(height / 10);

  let price = varByCode(variables, 'POLSTAR_ZAKLAD');
  price += size * varByCode(variables, 'POLSTAR_10CM');
  price += complex ? varByCode(variables, 'USITI_POLSTAR_C') : varByCode(variables, 'USITI_POLSTAR');

  if (stuffing) {
    price += varByCode(variables, 'POLSTAR_VYPLN_MIN_CENA');
    const minSize = varByCode(variables, 'POLSTAR_VYPLN_MIN');
    if (size * 10 > minSize) {
      price += Math.ceil((size * 10 - minSize) / 10) * varByCode(variables, 'POLSTAR_VYPLN_10CM');
    }
  }

  if (input.zipper) {
    price += varByCode(variables, 'USITI_ZALOZKA');
  }

  return roundPrice(price);
}

/**
 * Sedák – původní aplikace chyběla; vzorec odvozen z ceníkových kódů.
 * Ověřte s majitelem firmy před nasazením.
 */
export function calculateCushion(variables, input) {
  const width = parseFloat(input.width) || 0;
  const height = parseFloat(input.height) || 0;
  const depth = parseFloat(input.depth) || 8;
  const areaM2 = (width / 100) * (height / 100);

  let price = varByCode(variables, 'USITI_SEDAK');
  price += areaM2 * varByCode(variables, 'PRIPRAVA');

  if (input.foam !== false) {
    const layers = Math.max(1, Math.ceil(depth / 2));
    price += areaM2 * varByCode(variables, 'MOLITAN_2CM') * layers;
  }

  if (input.felt) {
    // ROUNO_025CM2 = cena za jednotku 0,25 cm²; 4 jednotky = 1 cm²
    const units = (width * height) * 4;
    price += (units / 10000) * varByCode(variables, 'ROUNO_025CM2') * 100;
  }

  if (input.ties) {
    price += varByCode(variables, 'USITI_UVAZ');
  }

  return roundPrice(price);
}

/**
 * Římská roleta – původní aplikace chyběla; vzorec od ceníku.
 */
export function calculateBlind(variables, input) {
  const width = parseFloat(input.width) || 0;
  const height = parseFloat(input.height) || 0;
  let tunnels = parseInt(input.tunnels, 10);
  if (!tunnels || tunnels < 1) {
    tunnels = Math.max(3, Math.ceil(height / 25));
  }

  let price = varByCode(variables, 'KOMPLET_ROLETA');
  price += (width / 100) * varByCode(variables, 'TUNYLKY_ROLETA');
  price += (height / 100) * varByCode(variables, 'PRIPRAVA');
  price += tunnels * varByCode(variables, 'USITI_ZALOZKA');

  return roundPrice(price);
}

export { STUHY };
