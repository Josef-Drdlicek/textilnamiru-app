import { PRODUCT_TYPES } from '../config/defaults.js';
import { formatMoney } from '../core/pricing.js';

export function renderSchema(item) {
  const typeLabel = PRODUCT_TYPES[item.type]?.label || item.type;
  const params = item.params || {};
  const rows = Object.entries(params)
    .filter(([, v]) => v !== '' && v != null && v !== false)
    .map(([k, v]) => `<tr><th>${escapeHtml(labelFor(k))}</th><td>${formatParam(v)}</td></tr>`)
    .join('');

  return `
    <div class="schema-sheet" id="schema-print">
      <header class="schema-header">
        <h2>Výrobní list</h2>
        <p><strong>${escapeHtml(typeLabel)}</strong> · ${item.count || 1}× · č. ${item.item_id || '—'}</p>
      </header>
      <div class="schema-body">
        <div class="schema-diagram">
          ${diagramSvg(item)}
        </div>
        <table class="schema-table">
          <tbody>${rows}</tbody>
        </table>
      </div>
      <footer class="schema-footer">
        <p>Cena bez DPH: ${formatMoney(item.price_net || 0)} Kč · s DPH: ${formatMoney(item.price_vat || 0)} Kč</p>
      </footer>
    </div>
  `;
}

function diagramSvg(item) {
  const w = Math.min(280, parseFloat(item.params?.width) || 120);
  const h = Math.min(200, parseFloat(item.params?.height) || 80);
  const scale = Math.min(240 / w, 160 / h, 1.5);
  const rw = w * scale;
  const rh = h * scale;
  const ox = (300 - rw) / 2;
  const oy = (180 - rh) / 2;

  return `
    <svg viewBox="0 0 300 200" class="schema-svg" aria-label="Náčrt rozměrů">
      <rect x="${ox}" y="${oy}" width="${rw}" height="${rh}" fill="none" stroke="#141b1e" stroke-width="2"/>
      <text x="${ox + rw / 2}" y="${oy + rh + 18}" text-anchor="middle" font-size="12">${w} cm</text>
      <text x="${ox - 8}" y="${oy + rh / 2}" text-anchor="end" font-size="12">${h} cm</text>
    </svg>
  `;
}

function labelFor(key) {
  const map = {
    width: 'Šířka',
    height: 'Výška / délka',
    depth: 'Tloušťka',
    material_width: 'Šířka látky',
    stuha: 'Stuha',
    ratio: 'Koeficient',
    ratio_custom: 'Vlastní koeficient',
    kanyr: 'Kanýr',
    kanyr_height: 'Výška kanýru',
    corners: 'Rohová olůvka',
    bottom: 'Spodní olůvko',
    narasit: 'Nařasit',
    snails: 'Šnečky',
    folding: 'Zalomení',
    stuffing: 'Výplň',
    complex: 'Složitý tvar',
    zipper: 'Zip',
    foam: 'Molitan',
    felt: 'Rouno',
    ties: 'Úvazy',
    tunnels: 'Tunýlky',
    count: 'Počet',
    note: 'Poznámka',
  };
  return map[key] || key;
}

function formatParam(v) {
  if (typeof v === 'boolean') return v ? 'Ano' : 'Ne';
  return escapeHtml(String(v));
}

function escapeHtml(s) {
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
