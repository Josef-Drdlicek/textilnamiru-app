import { initStorage, getVariables, saveVariables, getStuhy, saveStuhy, getUsers, saveUsers, getOrders, upsertOrder, deleteOrder, nextOrderId, getDraftOrder, setDraftOrder, getOrderById, getQuotes, upsertQuote, deleteQuote, nextQuoteId, getQuoteById } from './core/storage.js';
import { login, logout, currentUser, requireAuth } from './core/auth.js';
import { withVat, formatMoney } from './core/pricing.js';
import { ORDER_STATUS, PRODUCT_TYPES, STUHY } from './config/defaults.js';
import {
  calculateCurtain,
  calculateDrape,
  calculatePillow,
  calculateCushion,
  calculateBlind,
} from './calculators/index.js';
import { renderSchema } from './schema.js';

const CALCULATORS = {
  curtain: calculateCurtain,
  drape: calculateDrape,
  pillow: calculatePillow,
  cushion: calculateCushion,
  blind: calculateBlind,
};

const app = document.getElementById('app');

// Kořen veřejného webu (stejný host). Logo i odkaz zpět fungují i pod /b2b/aplikace.
const WEB_HOME_URL = '/';
// Logo: public/images/logo.jpg (stejný soubor jako na veřejném webu)
const LOGO_FILE = 'images/logo.jpg';

let nabidkaOznaceniChyba = false;
let zakazkaOznaceniChyba = false;
let adminHlaseniSmazani = false;

window.addEventListener('hashchange', render);

function route() {
  const raw = location.hash.replace(/^#\/?/, '') || 'login';
  const parts = raw.split('/').filter(Boolean);
  const path = parts.join('/') || 'login';
  return { path, parts, raw };
}

function navigate(path) {
  location.hash = `#/${path}`;
}

function render() {
  const { path, parts } = route();
  document.documentElement.lang = 'cs';

  if (path.startsWith('admin/uzivatel/')) {
    return guard(true, () => renderAdminUserProfile(parts[2]));
  }
  if (path.startsWith('zakazka/')) {
    return guard(false, () => renderOrder(parts[1]));
  }
  if (path.startsWith('produkt/')) {
    return guard(false, () => renderProductForm(parts[1], parts[2]));
  }
  if (path.startsWith('schema/')) {
    return guard(false, () => renderSchemaPage(parts[1], parts[2]));
  }
  if (path.startsWith('materialy/')) {
    return guard(false, () => renderMaterials(parts[1]));
  }
  if (path.startsWith('studio/nabidka/')) {
    return guard(false, () => {
      const id = parts[2];
      if (parts[3] === 'produkt') return renderStudioProdukt(id, parts[4]);
      if (parts[3] === 'schema') return renderStudioSchema(id, parts[4]);
      if (parts[3] === 'materialy') return renderStudioMaterialy(id);
      return renderStudioNabidka(id);
    });
  }

  switch (path) {
    case 'login':
      renderLogin();
      bindCommonEvents();
      return;
    case 'logout':
      logout();
      setDraftOrder(null);
      if (typeof window.__TNM_CSRF === 'string' && window.__TNM_CSRF) {
        odhlasitZLaravelu();
        return;
      }
      return navigate('login');
    case 'welcome':
      return guard(false, renderWelcome);
    case 'studio/cenove-nabidky':
      return guard(false, renderStudioCenoveNabidky);
    case 'studio/plan-vyroby':
      return guard(false, (user) => renderStudioPlaceholder(user, 'Plán výroby', 'plan-vyroby'));
    case 'admin':
      return guard(true, renderAdminOrders);
    case 'admin/kalendar':
      return guard(true, (user) => renderAdminPlaceholder(user, 'Kalendář výroby', 'kalendar'));
    case 'admin/cenik':
      return guard(true, renderAdminPricing);
    case 'admin/cenik-stropu':
      return guard(true, (user) => renderAdminPlaceholder(user, 'Ceník stropů', 'cenik-stropu'));
    case 'admin/cenik-rolet':
      return guard(true, (user) => renderAdminPlaceholder(user, 'Ceník rolet', 'cenik-rolet'));
    case 'admin/uzivatele':
      return guard(true, renderAdminUsers);
    case 'potvrzeni':
      return guard(false, renderConfirmation);
    default:
      return navigate('login');
  }
}

function guard(admin, fn) {
  const auth = requireAuth(admin);
  if (!auth.ok) return navigate(auth.redirect);
  // Obrazovky už HTML vykreslí samy; přepsat smíme jen když vrátí řetězec.
  const html = fn(auth.user);
  if (typeof html === 'string') app.innerHTML = html;
  bindCommonEvents(auth.user);
}

const NAV_POLOZKY = {
  admin: [
    { id: 'zakazky', href: '#/admin', label: 'Zakázky' },
    { id: 'kalendar', href: '#/admin/kalendar', label: 'Kalendář výroby' },
    { id: 'cenik', href: '#/admin/cenik', label: 'Ceník' },
    { id: 'cenik-stropu', href: '#/admin/cenik-stropu', label: 'Ceník stropů' },
    { id: 'cenik-rolet', href: '#/admin/cenik-rolet', label: 'Ceník rolet' },
    { id: 'uzivatele', href: '#/admin/uzivatele', label: 'Uživatelé' },
  ],
  studio: [
    { id: 'cenove-nabidky', href: '#/studio/cenove-nabidky', label: 'Cenové nabídky' },
    { id: 'zakazky', href: '#/welcome', label: 'Zakázky' },
    { id: 'plan-vyroby', href: '#/studio/plan-vyroby', label: 'Plán výroby' },
  ],
};

function navHtml(role, active) {
  const polozky = NAV_POLOZKY[role] || [];

  return polozky
    .map((polozka) => {
      const vybrano = active === polozka.id;
      return `
        <a
          href="${polozka.href}"
          class="admin-nav-link${vybrano ? ' is-active' : ''}"
          ${vybrano ? 'aria-current="page"' : ''}
        >${polozka.label}</a>`;
    })
    .join('');
}

function shell(user, title, body, options = {}) {
  const mode = options.mode || 'customer';
  const roleMenu = user.admin ? 'admin' : 'studio';
  const active = options.active || (user.admin ? null : 'zakazky');
  const profileHref =
    mode === 'admin'
      ? `#/admin/uzivatel/${user.user_id}`
      : '#/welcome';

  const logoHref = mode === 'admin' ? '#/admin' : '#/welcome';
  const maBocniMenu = true;

  const logo = `
          <div id="logo">
            <a href="${logoHref}" id="logo-link">
              <svg id="logo-svg" viewBox="0 0 220 48" role="img" aria-label="Textil na míru">
                <text x="0" y="34" font-family="Georgia, serif" font-size="26" font-weight="700" fill="#141b1e">Textil na míru</text>
              </svg>
            </a>
          </div>`;

  const hlavicka = `
      <header id="header">
        <nav id="menu" aria-label="Hlavní lišta">
          <button
            type="button"
            class="admin-nav-toggle"
            data-admin-nav-toggle
            aria-expanded="false"
            aria-controls="admin-sidebar"
          >Menu</button>
          ${logo}
        </nav>
      </header>`;

  const bocniPanel = `
      <div class="admin-nav-overlay" data-admin-nav-overlay hidden></div>
      <aside id="admin-sidebar" class="admin-sidebar">
        <nav class="admin-nav" aria-label="Hlavní menu">
          ${navHtml(roleMenu, active)}
        </nav>
      </aside>`;

  return `
    <div class="page${maBocniMenu ? ' admin-page' : ''}">
      ${hlavicka}

      <div id="customer">
        <div id="order" aria-hidden="true"></div>
        <div id="profile">
          <strong>Uživatel:</strong>
          <a href="${profileHref}">${esc(user.login)}</a>
          (<a href="#/logout" class="logout">Odhlásit se</a>)
        </div>
      </div>

      <div${maBocniMenu ? ' class="admin-body"' : ''}>
        ${bocniPanel}
        <main id="content" class="site-main">
          <h1 class="page-title">${title}</h1>
          ${body}
        </main>
      </div>
    </div>`;
}

function bindCommonEvents(user) {
  app.querySelectorAll('[data-action]').forEach((el) => {
    el.addEventListener('click', (e) => {
      const action = el.dataset.action;
      if (action === 'new-order') {
        const order = createEmptyOrder(user.user_id);
        setDraftOrder(order);
        navigate(`zakazka/${order.order_id}`);
      }
      if (action === 'new-quote') {
        const quote = createEmptyQuote(user.user_id);
        navigate(`studio/nabidka/${quote.quote_id}`);
      }
      if (action === 'open-quote') {
        e.preventDefault();
        navigate(`studio/nabidka/${el.dataset.quoteId}/materialy`);
      }
      if (action === 'delete-quote') {
        e.preventDefault();
        if (confirm('DOPLNIT TEXT')) {
          deleteQuote(parseInt(el.dataset.quoteId, 10));
          render();
        }
      }
      if (action === 'defer-quote') {
        e.preventDefault();
        odlozitNabidku(user);
      }
      if (action === 'quote-continue-materials') {
        e.preventDefault();
        pokracovatNaMaterialyNabidky(user, el.dataset.quoteId);
      }
      if (action === 'order-continue-materials') {
        e.preventDefault();
        pokracovatNaMaterialyZakazky(user, el.dataset.orderId);
      }
      if (action === 'order-action') {
        e.preventDefault();
        handleOrderAction(el.dataset.orderId, el.dataset.nextStatus);
      }
      if (action === 'delete-order') {
        e.preventDefault();
        smazatZakazkuAdminem(el.dataset.orderId);
      }
    });
  });

  const loginForm = document.getElementById('login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const fd = new FormData(loginForm);
      const result = login(fd.get('login'), fd.get('password'));
      const err = document.getElementById('login-error');
      if (!result.ok) {
        err.textContent = result.error;
        err.hidden = false;
        return;
      }
      navigate(result.user.admin ? 'admin' : 'welcome');
    });
  }

  bindProductForms();
  bindQuoteProductForm();
  bindPricingForm();
  bindPricingStuhyForm();
  bindPricingAddForm();
  bindPricingDeleteButtons();
  bindUserForm();
  bindMaterialsForm();
  bindQuoteMaterialsForm();
  bindAdminNav();
}

function bindAdminNav() {
  const prepinac = app.querySelector('[data-admin-nav-toggle]');
  const panel = document.getElementById('admin-sidebar');
  const prekryv = app.querySelector('[data-admin-nav-overlay]');
  if (!prepinac || !panel) {
    return;
  }

  const zavrit = () => {
    panel.classList.remove('is-open');
    if (prekryv) {
      prekryv.hidden = true;
    }
    prepinac.setAttribute('aria-expanded', 'false');
  };

  const otevrit = () => {
    panel.classList.add('is-open');
    if (prekryv) {
      prekryv.hidden = false;
    }
    prepinac.setAttribute('aria-expanded', 'true');
  };

  prepinac.addEventListener('click', () => {
    if (panel.classList.contains('is-open')) {
      zavrit();
    } else {
      otevrit();
    }
  });

  prekryv?.addEventListener('click', zavrit);
  panel.querySelectorAll('a').forEach((odkaz) => {
    odkaz.addEventListener('click', zavrit);
  });
}

function createEmptyOrder(userId) {
  const order = {
    order_id: nextOrderId(),
    user_id: userId,
    status: 2,
    created_at: new Date().toISOString(),
    items: [],
    materials: '',
    deadline: '',
    oznaceni: '',
    submitted: false,
  };
  upsertOrder(order);
  return order;
}

/** Stejné uložení odeslané zakázky pro běžný formulář i pro Cenové nabídky. */
function odeslatObjednavku(order) {
  order.submitted = true;
  order.status = 2;
  upsertOrder(order);
  setDraftOrder(null);
}

function createEmptyQuote(userId) {
  const quote = {
    quote_id: nextQuoteId(),
    user_id: userId,
    created_at: new Date().toISOString(),
    deferred_at: null,
    items: [],
    materials: '',
    deadline: '',
    oznaceni: '',
  };
  upsertQuote(quote);
  return quote;
}

function jeOznaceniVyplnene(quote) {
  return String(quote?.oznaceni || '').trim() !== '';
}

function vlastniNabidka(quoteId, user) {
  const quote = getQuoteById(parseInt(quoteId, 10));
  if (!quote || quote.user_id !== user.user_id) return null;
  return quote;
}

function soucetNabidky(quote) {
  return (quote.items || []).reduce((sum, item) => sum + item.price_vat * item.count, 0);
}

function soucetNabidkyBezDph(quote) {
  return (quote.items || []).reduce((sum, item) => sum + item.price_net * item.count, 0);
}

function getOrder(orderId) {
  const id = parseInt(orderId, 10);
  return getOrderById(id) || getDraftOrder();
}

function renderLogin() {
  const logoSrc = `${WEB_HOME_URL}${LOGO_FILE}`;
  app.innerHTML = `
    <div class="login-page">
      <div class="login-card">
        <h1 class="login-brand-heading">
          <a class="login-brand" href="${WEB_HOME_URL}" aria-label="Zpět na úvodní stránku webu">
            <span class="login-brand-logo"><img src="${logoSrc}" alt="Textil na míru" width="36" height="36"></span>
            Textil na míru
          </a>
        </h1>
        <p class="login-sub">Přihlášení do systému zakázek</p>
        <form id="login-form" class="form">
          <label>E-mail / login
            <input type="text" name="login" required autocomplete="username" value="lucien">
          </label>
          <label>Heslo
            <input type="password" name="password" required autocomplete="current-password" value="lucien">
          </label>
          <p id="login-error" class="form-error" hidden></p>
          <button type="submit" class="btn btn-primary">Přihlásit se</button>
        </form>
        <p class="login-hint">Demo: admin <code>info@textilnamiru.cz</code> / <code>admin</code></p>
      </div>
    </div>`;
}

function htmlSeznamZakazek(user) {
  const orders = getOrders().filter((o) => o.user_id === user.user_id);
  const rows =
    orders.length === 0
      ? '<tr><td colspan="5" class="empty">Zatím nemáte žádné zakázky.</td></tr>'
      : orders
          .map((o) => {
            const smazat =
              !user.admin && o.status === 5
                ? `<button type="button" class="btn btn-sm btn-danger" data-action="delete-order" data-order-id="${o.order_id}">Smazat</button>`
                : '';
            return `
      <tr>
        <td>${esc(o.oznaceni || '')}</td>
        <td>${formatDate(o.created_at)}</td>
        <td>${formatMoney(soucetNabidkyBezDph(o))} Kč</td>
        <td>${ORDER_STATUS[o.status]?.label || o.status}</td>
        <td class="actions"><a href="#/zakazka/${o.order_id}" class="btn btn-sm">Detail</a><span class="akce-smazat-misto">${smazat}</span></td>
      </tr>`;
          })
          .join('');

  return `
    <div class="toolbar">
      <button type="button" class="btn btn-primary" data-action="new-order">Nová zakázka</button>
    </div>
    <div class="table-wrap seznam-zakazek">
      <table class="data-table">
        <colgroup>
          <col class="col-nazev">
          <col class="col-datum">
          <col class="col-cena-bez">
          <col class="col-stav">
          <col class="col-akce">
        </colgroup>
        <thead><tr><th>Název zakázky</th><th>Vytvořeno</th><th>Cena bez DPH</th><th>Stav</th><th class="actions"></th></tr></thead>
        <tbody>${rows}</tbody>
      </table>
    </div>`;
}

function renderWelcome(user) {
  const hlaseni = adminHlaseniSmazani
    ? '<div class="success-box"><p>Zakázka byla smazána</p></div>'
    : '';
  adminHlaseniSmazani = false;
  app.innerHTML = shell(user, 'Moje zakázky', hlaseni + htmlSeznamZakazek(user), {
    mode: 'customer',
    active: 'zakazky',
  });
}

function renderStudioCenoveNabidky(user) {
  const odlozene = getQuotes().filter((q) => q.user_id === user.user_id && q.deferred_at);
  const rows =
    odlozene.length === 0
      ? '<tr><td colspan="5" class="empty">DOPLNIT TEXT</td></tr>'
      : odlozene
          .map(
            (q) => `
      <tr>
        <td>${esc(q.oznaceni || '')}</td>
        <td>${formatDate(q.deferred_at)}</td>
        <td>${formatMoney(soucetNabidkyBezDph(q))} Kč</td>
        <td>${formatMoney(soucetNabidky(q))} Kč</td>
        <td class="actions">
          <button type="button" class="btn btn-sm btn-success" data-action="open-quote" data-quote-id="${q.quote_id}">Upravit</button>
          <button type="button" class="btn btn-sm btn-danger" data-action="delete-quote" data-quote-id="${q.quote_id}">Smazat</button>
        </td>
      </tr>`
          )
          .join('');

  app.innerHTML = shell(
    user,
    'Cenové nabídky',
    `
    <div class="toolbar">
      <button type="button" class="btn btn-primary" data-action="new-quote">Nová zakázka</button>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Název zakázky</th><th>Datum zadání</th><th>Cena bez DPH</th><th>Cena s DPH</th><th></th></tr></thead>
        <tbody>${rows}</tbody>
      </table>
    </div>`,
    { mode: 'customer', active: 'cenove-nabidky' }
  );
}

function renderStudioPlaceholder(user, title, active) {
  app.innerHTML = shell(user, title, '<p>DOPLNIT OBSAH</p>', { mode: 'customer', active });
}

function renderStudioNabidka(quoteId) {
  const user = currentUser();
  const quote = vlastniNabidka(quoteId, user);
  if (!quote) return navigate('studio/cenove-nabidky');

  const productLinks = Object.entries(PRODUCT_TYPES)
    .map(
      ([key, p]) =>
        `<a href="#/studio/nabidka/${quote.quote_id}/produkt/${key}" class="product-card"><strong>${p.label}</strong><span>Přidat položku</span></a>`
    )
    .join('');

  const itemRows =
    quote.items.length === 0
      ? '<tr><td colspan="4" class="empty">Zakázka je prázdná – přidejte produkt.</td></tr>'
      : quote.items
          .map(
            (item, idx) => `
      <tr>
        <td>${PRODUCT_TYPES[item.type]?.label || item.type}</td>
        <td>${item.count}×</td>
        <td>${formatMoney(item.price_vat * item.count)} Kč</td>
        <td>
          <a href="#/studio/nabidka/${quote.quote_id}/schema/${idx}" class="btn btn-sm">Schéma</a>
          <button type="button" class="btn btn-sm btn-danger" data-remove-quote-item="${idx}">Odebrat</button>
        </td>
      </tr>`
          )
          .join('');

  const chybaOznaceni = nabidkaOznaceniChyba;
  nabidkaOznaceniChyba = false;

  app.innerHTML = shell(
    user,
    `<label for="quote-oznaceni">Název zakázky</label> <input type="text" id="quote-oznaceni" class="quote-oznaceni-pole" value="${esc(quote.oznaceni || '')}" aria-invalid="${chybaOznaceni ? 'true' : 'false'}" aria-describedby="quote-oznaceni-error">`,
    `
    <p id="quote-oznaceni-error" class="form-error"${chybaOznaceni ? '' : ' hidden'}>není vyplněno</p>
    <section class="panel">
      <h2>Přidat produkt</h2>
      <div class="product-grid">${productLinks}</div>
    </section>
    <section class="panel">
      <h2>Položky</h2>
      <table class="data-table">
        <thead><tr><th>Produkt</th><th>Počet</th><th>Cena s DPH</th><th></th></tr></thead>
        <tbody>${itemRows}</tbody>
      </table>
      ${
        quote.items.length
          ? `<div class="toolbar"><button type="button" class="btn btn-primary" data-action="quote-continue-materials" data-quote-id="${quote.quote_id}">Pokračovat na materiály</button></div>`
          : ''
      }
    </section>`,
    { mode: 'customer', active: 'cenove-nabidky' }
  );

  const poleOznaceni = document.getElementById('quote-oznaceni');
  const hlaseniOznaceni = document.getElementById('quote-oznaceni-error');
  if (poleOznaceni) {
    poleOznaceni.addEventListener('input', () => {
      quote.oznaceni = poleOznaceni.value;
      upsertQuote(quote);
      poleOznaceni.setAttribute('aria-invalid', 'false');
      if (hlaseniOznaceni) hlaseniOznaceni.hidden = true;
    });
    poleOznaceni.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter' || !quote.items.length) return;
      e.preventDefault();
      pokracovatNaMaterialyNabidky(user, quote.quote_id);
    });
  }

  app.querySelectorAll('[data-remove-quote-item]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const idx = parseInt(btn.dataset.removeQuoteItem, 10);
      quote.items.splice(idx, 1);
      upsertQuote(quote);
      render();
    });
  });
}

function pokracovatNaMaterialyNabidky(user, quoteId) {
  const quote = vlastniNabidka(quoteId, user);
  if (!quote) return navigate('studio/cenove-nabidky');

  const pole = document.getElementById('quote-oznaceni');
  if (pole) quote.oznaceni = pole.value;
  upsertQuote(quote);

  if (!jeOznaceniVyplnene(quote)) {
    nabidkaOznaceniChyba = true;
    if (pole) pole.setAttribute('aria-invalid', 'true');
    const hlaseni = document.getElementById('quote-oznaceni-error');
    if (hlaseni) hlaseni.hidden = false;
    return;
  }

  navigate(`studio/nabidka/${quote.quote_id}/materialy`);
}

function renderStudioProdukt(quoteId, type) {
  const user = currentUser();
  const quote = vlastniNabidka(quoteId, user);
  if (!quote) return navigate('studio/cenove-nabidky');

  const meta = PRODUCT_TYPES[type];
  if (!meta) return navigate(`studio/nabidka/${quoteId}`);

  const defaults = defaultParams(type);
  const formHtml = productFormHtml(type, defaults);

  app.innerHTML = shell(
    user,
    meta.label,
    `
    <form id="quote-product-form" class="form product-form" data-type="${type}" data-quote-id="${quoteId}">
      ${formHtml}
      <div class="price-box">
        <p>Cena / ks bez DPH: <strong id="price-unit">0</strong> Kč</p>
        <p>Cena celkem bez DPH: <strong id="price-total">0</strong> Kč</p>
        <p>Cena / ks s DPH: <strong id="price-unit-vat">0</strong> Kč</p>
        <p>Cena celkem s DPH: <strong id="price-total-vat">0</strong> Kč</p>
      </div>
      <div class="toolbar">
        <a href="#/studio/nabidka/${quoteId}" class="btn btn-muted">Zpět</a>
        <button type="submit" class="btn btn-primary">Přidat do zakázky</button>
      </div>
    </form>`,
    { mode: 'customer', active: 'cenove-nabidky' }
  );

  updateProductPrice(type);
}

function renderStudioSchema(quoteId, itemIdx) {
  const user = currentUser();
  const quote = vlastniNabidka(quoteId, user);
  const item = quote?.items[parseInt(itemIdx, 10)];
  if (!item) return navigate(`studio/nabidka/${quoteId}`);

  app.innerHTML = shell(
    user,
    'Výrobní schéma',
    `
    ${renderSchema(item)}
    <div class="toolbar">
      <button type="button" class="btn btn-muted" onclick="window.print()">Tisk</button>
      <a href="#/studio/nabidka/${quoteId}" class="btn btn-primary">Zpět na zakázku</a>
    </div>`,
    { mode: 'customer', active: 'cenove-nabidky' }
  );
}

function renderStudioMaterialy(quoteId) {
  const user = currentUser();
  const quote = vlastniNabidka(quoteId, user);
  if (!quote || !quote.items.length) return navigate(`studio/nabidka/${quoteId}`);
  if (!jeOznaceniVyplnene(quote)) {
    nabidkaOznaceniChyba = true;
    return navigate(`studio/nabidka/${quoteId}`);
  }

  app.innerHTML = shell(
    user,
    'Soupis materiálu',
    `
    <form id="quote-materials-form" class="form form-narrow">
      <label for="quote-materials-text">Materiály (látka, barva, dodavatel…)
        <textarea id="quote-materials-text" name="materials" rows="6" required>${esc(quote.materials || '')}</textarea>
      </label>
      <label for="quote-deadline">Termín dodání
        <input type="date" id="quote-deadline" name="deadline" value="${quote.deadline || ''}" required>
      </label>
      <div class="toolbar">
        <a href="#/studio/nabidka/${quote.quote_id}" class="btn btn-muted">Zpět</a>
        <button type="button" class="btn btn-muted" data-action="defer-quote">Odložit objednávku</button>
        <button type="submit" class="btn btn-primary">Odeslat objednávku</button>
      </div>
    </form>`,
    { mode: 'customer', active: 'cenove-nabidky' }
  );
}

function nazevZakazkyProSeznam(order) {
  const nazev = String(order?.oznaceni || '').trim();
  return nazev || String(order.order_id);
}

function renderAdminOrders(user) {
  const orders = getOrders();
  const sections = [
    { key: 2, title: 'Nové zakázky' },
    { key: 3, title: 'Přijaté' },
    { key: 4, title: 'K fakturaci' },
    { key: 5, title: 'Hotové' },
  ];

  const hlaseni = adminHlaseniSmazani
    ? '<div class="success-box"><p>Zakázka byla smazána</p></div>'
    : '';
  adminHlaseniSmazani = false;

  const html = sections
    .map(({ key, title }) => {
      const list = orders.filter((o) => o.status === key);
      const rows =
        list.length === 0
          ? '<tr><td colspan="7" class="empty">—</td></tr>'
          : list
              .map((o) => {
                const st = ORDER_STATUS[key];
                const actionBtn = st?.action
                  ? `<button type="button" class="btn btn-sm btn-primary" data-action="order-action" data-order-id="${o.order_id}" data-next-status="${st.next}">${st.action}</button>`
                  : '';
                const delBtn = user.admin
                  ? `<button type="button" class="btn btn-sm btn-danger smazat-na-pravitku" data-action="delete-order" data-order-id="${o.order_id}">Smazat</button>`
                  : '';
                return `<tr>
                  <td>${esc(getUserName(o.user_id))}</td>
                  <td>${esc(nazevZakazkyProSeznam(o))}</td>
                  <td>${formatDate(o.created_at)}</td>
                  <td class="col-mezera-hi" aria-hidden="true"></td>
                  <td>${formatMoney(soucetNabidkyBezDph(o))} Kč</td>
                  <td>${formatMoney(soucetNabidky(o))} Kč</td>
                  <td class="actions">${actionBtn}${delBtn}</td>
                </tr>`;
              })
              .join('');
      return `
        <section class="order-section">
          <h2>${title} (${list.length})</h2>
          <table class="data-table">
            <colgroup>
              <col class="col-zakaznik">
              <col class="col-nazev">
              <col class="col-datum">
              <col class="col-mezera-hi">
              <col class="col-cena-bez">
              <col class="col-cena-dph">
              <col class="col-akce">
            </colgroup>
            <thead><tr><th>Zákazník</th><th>Název zakázky</th><th>Datum</th><th class="col-mezera-hi" aria-hidden="true"></th><th>Cena bez DPH</th><th>Cena vč. DPH</th><th class="actions">Akce</th></tr></thead>
            <tbody>${rows}</tbody>
          </table>
        </section>`;
    })
    .join('');

  app.innerHTML = shell(
    user,
    'Přehled zakázek',
    hlaseni +
      `<div class="order-tables-posuv seznam-zakazek-admin">${html}</div>`,
    { mode: 'admin', active: 'zakazky' }
  );
}

function renderAdminPlaceholder(user, title, active) {
  app.innerHTML = shell(user, title, '<p>DOPLNIT OBSAH</p>', { mode: 'admin', active });
}

function handleOrderAction(orderId, nextStatus) {
  const order = getOrderById(parseInt(orderId, 10));
  if (!order) return;
  order.status = parseInt(nextStatus, 10);
  upsertOrder(order);
  render();
}

function smiSmazatZakazku(user, order) {
  if (!user || !order) return false;
  if (user.admin) return true;
  return order.user_id === user.user_id && order.status === 5;
}

/** Mazání zakázky: admin vždy, studio jen vlastní zakázku ve stavu Hotové. */
function smazatZakazkuAdminem(orderId) {
  const user = currentUser();
  const id = parseInt(orderId, 10);
  const order = getOrderById(id);
  if (!smiSmazatZakazku(user, order)) return;

  const nazev = nazevZakazkyProSeznam(order);
  if (!confirm(`${nazev}\n\nOpravdu chcete zakázku smazat? Tuto akci nelze vrátit.`)) return;

  try {
    deleteOrder(id);
  } catch {
    return;
  }

  adminHlaseniSmazani = true;
  render();
}

function renderAdminPricing(user) {
  const variables = getVariables();
  const rows = variables
    .map((v) => {
      const smazat = v.custom
        ? `<button type="button" class="btn btn-sm btn-danger" data-action="delete-price-row" data-code="${esc(v.code)}">Smazat</button>`
        : '';
      return `
    <tr>
      <td>${esc(v.name)}</td>
      <td><code>${esc(v.code)}</code></td>
      <td>${esc(v.unit ?? '')}</td>
      <td>
        <input type="number" step="0.01" min="0" name="var_${esc(v.code)}" value="${esc(v.value)}" class="input-sm">
      </td>
      <td>${smazat}</td>
    </tr>`;
    })
    .join('');

  app.innerHTML = shell(
    user,
    'Ceník',
    `
    <form id="pricing-form" class="form">
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th>Název</th><th>Kód</th><th>Jednotka</th><th>Hodnota (Kč / %)</th><th></th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
      <button type="submit" class="btn btn-primary">Uložit ceník</button>
    </form>
    ${stuhySectionHtml()}
    <form id="pricing-add-form" class="form form-narrow">
      <p>Přidat řádek</p>
      <label>Název
        <input type="text" name="name" required>
      </label>
      <label>Jednotka
        <input type="text" name="unit" required placeholder="např. m2, m, km, ks">
      </label>
      <label>Cena v Kč
        <input type="number" name="value" step="0.01" min="0" required>
      </label>
      <p id="pricing-add-error" class="form-error" hidden></p>
      <button type="submit" class="btn btn-primary">Přidat řádek</button>
    </form>`,
    { mode: 'admin', active: 'cenik' }
  );
}

/** Převezme aktuální ceny z formuláře, ostatní pole položky ponechá. */
function pricingFromForm() {
  const form = document.getElementById('pricing-form');
  const variables = getVariables();
  if (!form) return variables;
  const fd = new FormData(form);
  return variables.map((v) => ({
    ...v,
    value: String(fd.get(`var_${v.code}`) ?? v.value),
  }));
}

function bindPricingForm() {
  const form = document.getElementById('pricing-form');
  if (!form) return;
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    saveVariables(pricingFromForm());
    alert('Ceník uložen.');
  });
}

function stuhySectionHtml() {
  const stored = getStuhy();
  const rows = Object.keys(STUHY)
    .map((code) => {
      const def = STUHY[code];
      const item = stored[code] || { price: def.price, ratios: def.ratios };
      const ratios = (item.ratios || []).join(', ');
      const ratiosDisabled = code === 'custom' ? ' disabled' : '';
      return `
    <tr>
      <td>${esc(def.label || code)}</td>
      <td><code>${esc(code)}</code></td>
      <td>
        <input type="number" step="0.01" min="0" name="price_${esc(code)}" value="${esc(item.price)}" class="input-sm">
      </td>
      <td>
        <input type="text" name="ratios_${esc(code)}" value="${esc(ratios)}"${ratiosDisabled} class="input-sm input-ratios">
      </td>
    </tr>`;
    })
    .join('');

  return `
    <section class="pricing-section">
      <h2>Řasící stuhy</h2>
      <p>Poměry řasení oddělujte čárkou — např. „1.5, 2, 2.5“.</p>
      <form id="stuhy-form" class="form">
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Název</th><th>Kód</th><th>Cena za metr (Kč)</th><th>Povolené poměry</th></tr></thead>
            <tbody>${rows}</tbody>
          </table>
        </div>
        <button type="submit" class="btn btn-primary">Uložit stuhy</button>
      </form>
    </section>`;
}

function bindPricingStuhyForm() {
  const form = document.getElementById('stuhy-form');
  if (!form) return;
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const next = {};
    for (const code of Object.keys(STUHY)) {
      const price = Number(String(fd.get(`price_${code}`) ?? '').replace(',', '.'));
      const ratiosRaw = String(fd.get(`ratios_${code}`) ?? '');
      next[code] = {
        price: Number.isFinite(price) && price >= 0 ? price : STUHY[code].price,
        ratios:
          code === 'custom'
            ? []
            : ratiosRaw
                .split(',')
                .map((s) => s.trim())
                .filter(Boolean),
      };
    }
    saveStuhy(next);
    alert('Stuhy uloženy.');
  });
}

/** Klíč z názvu: bez diakritiky, bez mezer, unikátní. */
function kodZNazvu(name, used) {
  const zaklad =
    String(name)
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-zA-Z0-9]+/g, '_')
      .replace(/^_|_$/g, '')
      .toUpperCase() || 'RADKA';
  let code = zaklad;
  let n = 2;
  while (used.has(code)) {
    code = `${zaklad}_${n}`;
    n += 1;
  }
  return code;
}

function dalsiVarId(variables) {
  const max = variables.reduce((m, v) => {
    const n = parseInt(v.var_id, 10);
    return Number.isFinite(n) ? Math.max(m, n) : m;
  }, 0);
  return String(max + 1);
}

function bindPricingAddForm() {
  const form = document.getElementById('pricing-add-form');
  if (!form) return;
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const err = document.getElementById('pricing-add-error');
    const fd = new FormData(form);
    const name = String(fd.get('name') ?? '').trim();
    const unit = String(fd.get('unit') ?? '').trim();
    const rawValue = String(fd.get('value') ?? '').trim().replace(',', '.');
    const value = Number(rawValue);

    let hlaska = '';
    if (!name) hlaska = 'Zadejte název.';
    else if (!unit) hlaska = 'Zadejte jednotku (například m2, m, km nebo ks).';
    else if (rawValue === '' || !Number.isFinite(value) || value < 0) {
      hlaska = 'Zadejte cenu jako číslo 0 nebo větší.';
    }
    if (hlaska) {
      err.textContent = hlaska;
      err.hidden = false;
      return;
    }

    const variables = pricingFromForm();
    const code = kodZNazvu(
      name,
      new Set(variables.map((v) => v.code))
    );
    variables.push({
      var_id: dalsiVarId(variables),
      name,
      code,
      unit,
      value: String(value),
      custom: true,
    });
    saveVariables(variables);
    render();
  });
}

function bindPricingDeleteButtons() {
  app.querySelectorAll('[data-action="delete-price-row"]').forEach((el) => {
    el.addEventListener('click', () => {
      const code = el.dataset.code;
      const item = getVariables().find((v) => v.code === code);
      if (!item?.custom) return;
      if (!confirm(`Opravdu smazat řádek „${item.name}“?`)) return;
      const variables = pricingFromForm().filter((v) => v.code !== code);
      saveVariables(variables);
      render();
    });
  });
}

function renderAdminUsers(user) {
  const users = getUsers();
  const rows = users
    .map(
      (u) => `
    <tr>
      <td>${u.name}</td>
      <td>${u.login}</td>
      <td>${u.admin ? 'Ano' : 'Ne'}</td>
      <td>${u.active !== false ? 'Ano' : 'Ne'}</td>
      <td><a href="#/admin/uzivatel/${u.user_id}" class="btn btn-sm">Profil</a></td>
    </tr>`
    )
    .join('');

  app.innerHTML = shell(
    user,
    'Uživatelé',
    `
    <div class="toolbar">
      <a href="#/admin/uzivatel/0" class="btn btn-primary">Nový uživatel</a>
    </div>
    <table class="data-table">
      <thead><tr><th>Jméno</th><th>Login</th><th>Admin</th><th>Aktivní</th><th></th></tr></thead>
      <tbody>${rows}</tbody>
    </table>`,
    { mode: 'admin', active: 'uzivatele' }
  );
}

function renderAdminUserProfile(userIdStr) {
  const userId = parseInt(userIdStr, 10);
  const isNew = userId === 0;
  const profile = isNew
    ? { user_id: 0, name: '', login: '', password: '', admin: false, active: true }
    : getUsers().find((u) => u.user_id === userId);

  if (!profile) return navigate('admin/uzivatele');

  app.innerHTML = shell(
    currentUser(),
    isNew ? 'Nový uživatel' : `Profil: ${profile.name}`,
    `
    <form id="user-form" class="form form-narrow">
      <label>Jméno<input type="text" name="name" value="${esc(profile.name)}" required></label>
      <label>Login<input type="text" name="login" value="${esc(profile.login)}" required></label>
      <label>Heslo ${isNew ? '' : '(prázdné = neměnit)'}<input type="password" name="password" autocomplete="new-password"></label>
      <label class="checkbox"><input type="checkbox" name="admin" ${profile.admin ? 'checked' : ''}> Administrátor</label>
      <label class="checkbox"><input type="checkbox" name="active" ${profile.active !== false ? 'checked' : ''}> Aktivní</label>
      <button type="submit" class="btn btn-primary">Uložit</button>
    </form>`,
    { mode: 'admin', active: 'uzivatele' }
  );
}

function bindUserForm() {
  const form = document.getElementById('user-form');
  if (!form) return;
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const users = getUsers();
    const pathId = parseInt(route().parts[2], 10);
    let user = users.find((u) => u.user_id === pathId);

    if (pathId === 0) {
      const newId = users.length ? Math.max(...users.map((u) => u.user_id)) + 1 : 1;
      user = { user_id: newId, name: '', login: '', password: '', admin: false, active: true };
      users.push(user);
    }

    user.name = fd.get('name');
    user.login = fd.get('login');
    user.admin = fd.has('admin');
    user.active = fd.has('active');
    const pwd = fd.get('password');
    if (pwd) user.password = pwd;

    saveUsers(users);
    navigate('admin/uzivatele');
  });
}

function renderOrder(orderId) {
  const user = currentUser();
  const order = getOrder(orderId);
  if (!order || order.user_id !== user.user_id) return navigate('welcome');

  setDraftOrder(order);

  const productLinks = Object.entries(PRODUCT_TYPES)
    .map(
      ([key, p]) =>
        `<a href="#/produkt/${order.order_id}/${key}" class="product-card"><strong>${p.label}</strong><span>Přidat položku</span></a>`
    )
    .join('');

  const itemRows =
    order.items.length === 0
      ? '<tr><td colspan="4" class="empty">Zakázka je prázdná – přidejte produkt.</td></tr>'
      : order.items
          .map(
            (item, idx) => `
      <tr>
        <td>${PRODUCT_TYPES[item.type]?.label || item.type}</td>
        <td>${item.count}×</td>
        <td>${formatMoney(item.price_vat * item.count)} Kč</td>
        <td>
          <a href="#/schema/${order.order_id}/${idx}" class="btn btn-sm">Schéma</a>
          <button type="button" class="btn btn-sm btn-danger" data-remove-item="${idx}">Odebrat</button>
        </td>
      </tr>`
          )
          .join('');

  const jeStudio = !user.admin;
  const chybaOznaceni = jeStudio && zakazkaOznaceniChyba;
  zakazkaOznaceniChyba = false;

  const nadpis = jeStudio
    ? `<label for="order-oznaceni">Název zakázky</label> <input type="text" id="order-oznaceni" class="quote-oznaceni-pole" value="${esc(order.oznaceni || '')}" aria-invalid="${chybaOznaceni ? 'true' : 'false'}" aria-describedby="order-oznaceni-error">`
    : `Zakázka č. ${order.order_id}`;

  const pokracovat = order.items.length
    ? jeStudio
      ? `<div class="toolbar"><button type="button" class="btn btn-primary" data-action="order-continue-materials" data-order-id="${order.order_id}">Pokračovat na materiály</button></div>`
      : `<div class="toolbar"><a href="#/materialy/${order.order_id}" class="btn btn-primary">Pokračovat na materiály</a></div>`
    : '';

  app.innerHTML = shell(
    user,
    nadpis,
    `
    ${jeStudio ? `<p id="order-oznaceni-error" class="form-error"${chybaOznaceni ? '' : ' hidden'}>není vyplněno</p>` : ''}
    <section class="panel">
      <h2>Přidat produkt</h2>
      <div class="product-grid">${productLinks}</div>
    </section>
    <section class="panel">
      <h2>Položky</h2>
      <table class="data-table">
        <thead><tr><th>Produkt</th><th>Počet</th><th>Cena s DPH</th><th></th></tr></thead>
        <tbody id="order-items">${itemRows}</tbody>
      </table>
      ${pokracovat}
    </section>`,
    { mode: 'customer' }
  );

  const poleOznaceni = document.getElementById('order-oznaceni');
  const hlaseniOznaceni = document.getElementById('order-oznaceni-error');
  if (poleOznaceni) {
    poleOznaceni.addEventListener('input', () => {
      order.oznaceni = poleOznaceni.value;
      upsertOrder(order);
      setDraftOrder(order);
      poleOznaceni.setAttribute('aria-invalid', 'false');
      if (hlaseniOznaceni) hlaseniOznaceni.hidden = true;
    });
    poleOznaceni.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter' || !order.items.length) return;
      e.preventDefault();
      pokracovatNaMaterialyZakazky(user, order.order_id);
    });
  }

  app.querySelectorAll('[data-remove-item]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const idx = parseInt(btn.dataset.removeItem, 10);
      order.items.splice(idx, 1);
      upsertOrder(order);
      setDraftOrder(order);
      render();
    });
  });
}

function pokracovatNaMaterialyZakazky(user, orderId) {
  const order = getOrder(parseInt(orderId, 10));
  if (!order || order.user_id !== user.user_id) return navigate('welcome');

  const pole = document.getElementById('order-oznaceni');
  if (pole) order.oznaceni = pole.value;
  upsertOrder(order);
  setDraftOrder(order);

  if (!jeOznaceniVyplnene(order)) {
    zakazkaOznaceniChyba = true;
    if (pole) pole.setAttribute('aria-invalid', 'true');
    const hlaseni = document.getElementById('order-oznaceni-error');
    if (hlaseni) hlaseni.hidden = false;
    return;
  }

  navigate(`materialy/${order.order_id}`);
}

function renderProductForm(orderId, type) {
  const user = currentUser();
  const order = getOrder(orderId);
  if (!order) return navigate('welcome');

  const meta = PRODUCT_TYPES[type];
  if (!meta) return navigate(`zakazka/${orderId}`);

  const defaults = defaultParams(type);
  const formHtml = productFormHtml(type, defaults);

  app.innerHTML = shell(
    user,
    meta.label,
    `
    <form id="product-form" class="form product-form" data-type="${type}" data-order-id="${orderId}">
      ${formHtml}
      <div class="price-box">
        <p>Cena / ks bez DPH: <strong id="price-unit">0</strong> Kč</p>
        <p>Cena celkem bez DPH: <strong id="price-total">0</strong> Kč</p>
        <p>Cena / ks s DPH: <strong id="price-unit-vat">0</strong> Kč</p>
        <p>Cena celkem s DPH: <strong id="price-total-vat">0</strong> Kč</p>
      </div>
      <div class="toolbar">
        <a href="#/zakazka/${orderId}" class="btn btn-muted">Zpět</a>
        <button type="submit" class="btn btn-primary">Přidat do zakázky</button>
      </div>
    </form>`,
    { mode: 'customer' }
  );

  updateProductPrice(type);
}

function defaultParams(type) {
  const base = { count: 1, width: 120, height: 80 };
  if (type === 'curtain' || type === 'drape') {
    return { ...base, stuha: 'siroky70', ratio: '2', material_width: 140, folding: 0 };
  }
  if (type === 'cushion') return { ...base, depth: 8, foam: true, felt: false, ties: false };
  if (type === 'blind') return { ...base, tunnels: 4 };
  if (type === 'pillow') return { ...base, stuffing: true, complex: false, zipper: false };
  return base;
}

function productFormHtml(type, d) {
  const common = `
    <label>Šířka (cm)<input type="number" step="0.1" name="width" id="width" value="${d.width}" required></label>
    <label>Výška (cm)<input type="number" step="0.1" name="height" id="height" value="${d.height}" required></label>
    <label>Počet kusů<input type="number" min="1" name="count" id="count" value="${d.count}" required></label>
    <label>Poznámka<textarea name="note" rows="2"></textarea></label>`;

  if (type === 'curtain' || type === 'drape') {
    const stuhaOpts = Object.keys(STUHY)
      .map((k) => `<option value="${k}" ${d.stuha === k ? 'selected' : ''}>${k}</option>`)
      .join('');
    const extra =
      type === 'drape'
        ? `<label>Šířka látky (cm)<input type="number" name="material_width" id="material_width" value="${d.material_width}"></label>
           <label>Zalomení<input type="number" step="0.1" name="folding" id="folding" value="${d.folding}"></label>`
        : '';
    return `
      ${extra}
      <label>Stuha<select name="stuha" id="stuha">${stuhaOpts}</select></label>
      <label>Koeficient<select name="ratio" id="ratio">${ratioOptions(d.stuha, d.ratio)}</select></label>
      <label>Vlastní koeficient<input type="number" step="0.01" name="ratio_custom" id="ratio_custom" value="2"></label>
      <label class="checkbox"><input type="checkbox" name="kanyr" id="kanyr"> Kanýr</label>
      <label>Výška kanýru (cm)<input type="number" name="kanyr_height" id="kanyr_height" value="15"></label>
      <label class="checkbox"><input type="checkbox" name="corners" id="corners"> Rohová olůvka</label>
      ${type === 'curtain' ? '<label class="checkbox"><input type="checkbox" name="bottom" id="bottom"> Spodní olůvko</label>' : ''}
      <label class="checkbox"><input type="checkbox" name="narasit" id="narasit"> Nařasit</label>
      <label class="checkbox"><input type="checkbox" name="snails" id="snails"> Šnečky</label>
      ${common}`;
  }

  if (type === 'pillow') {
    return `
      ${common}
      <label class="checkbox"><input type="checkbox" name="stuffing" id="stuffing" ${d.stuffing ? 'checked' : ''}> Výplň</label>
      <label class="checkbox"><input type="checkbox" name="complex" id="complex"> Složitý tvar</label>
      <label class="checkbox"><input type="checkbox" name="zipper" id="zipper"> Zip</label>`;
  }

  if (type === 'cushion') {
    return `
      ${common.replace('Výška', 'Délka')}
      <label>Tloušťka (cm)<input type="number" step="0.1" name="depth" id="depth" value="${d.depth}"></label>
      <label class="checkbox"><input type="checkbox" name="foam" id="foam" checked> Molitan</label>
      <label class="checkbox"><input type="checkbox" name="felt" id="felt"> Rouno</label>
      <label class="checkbox"><input type="checkbox" name="ties" id="ties"> Úvazy</label>`;
  }

  if (type === 'blind') {
    return `
      ${common}
      <label>Počet tunýlků<input type="number" min="1" name="tunnels" id="tunnels" value="${d.tunnels}"></label>`;
  }

  return common;
}

function ratioOptions(stuhaKey, selected) {
  const stuha = STUHY[stuhaKey] || STUHY.siroky70;
  const ratios = stuha.ratios.length ? stuha.ratios : ['1.5', '2', '2.5'];
  return ratios.map((r) => `<option value="${r}" ${r === selected ? 'selected' : ''}>${r}</option>`).join('');
}

function readProductForm(type) {
  const form = document.getElementById('product-form') || document.getElementById('quote-product-form');
  const fd = new FormData(form);
  const input = {};
  fd.forEach((v, k) => {
    input[k] = v;
  });
  ['kanyr', 'corners', 'bottom', 'narasit', 'snails', 'stuffing', 'complex', 'zipper', 'foam', 'felt', 'ties'].forEach(
    (k) => {
      input[k] = fd.has(k);
    }
  );
  if (type === 'cushion' && !fd.has('foam')) input.foam = false;
  return input;
}

function updateProductPrice(type) {
  const variables = getVariables();
  const input = readProductForm(type);
  const calc = CALCULATORS[type];
  if (!calc) return;

  const net = calc(variables, input);
  const netVat = withVat(net, variables);
  const count = parseInt(input.count, 10) || 1;

  document.getElementById('price-unit').textContent = formatMoney(net);
  document.getElementById('price-total').textContent = formatMoney(net * count);
  document.getElementById('price-unit-vat').textContent = formatMoney(netVat);
  document.getElementById('price-total-vat').textContent = formatMoney(netVat * count);
}

function bindQuoteProductForm() {
  const form = document.getElementById('quote-product-form');
  if (!form) return;

  const type = form.dataset.type;
  form.querySelectorAll('input, select, textarea').forEach((el) => {
    el.addEventListener('input', () => updateProductPrice(type));
    el.addEventListener('change', () => {
      if (el.id === 'stuha') refreshRatioSelect(el.value);
      updateProductPrice(type);
    });
  });

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const quoteId = parseInt(form.dataset.quoteId, 10);
    const quote = getQuoteById(quoteId);
    if (!quote) return navigate('studio/cenove-nabidky');
    const variables = getVariables();
    const input = readProductForm(type);
    const net = CALCULATORS[type](variables, input);
    const netVat = withVat(net, variables);
    const count = parseInt(input.count, 10) || 1;

    quote.items.push({
      item_id: `${quoteId}-${quote.items.length + 1}`,
      type,
      count,
      params: { ...input },
      price_net: net,
      price_vat: netVat,
    });
    upsertQuote(quote);
    navigate(`studio/nabidka/${quoteId}`);
  });
}

function odlozitNabidku(user) {
  const quoteId = parseInt(route().parts[2], 10);
  const quote = vlastniNabidka(quoteId, user);
  if (!quote) return navigate('studio/cenove-nabidky');

  const form = document.getElementById('quote-materials-form');
  if (form) {
    const fd = new FormData(form);
    quote.materials = fd.get('materials') || '';
    quote.deadline = fd.get('deadline') || '';
  }
  quote.deferred_at = new Date().toISOString();
  upsertQuote(quote);
  navigate('studio/cenove-nabidky');
}

function bindQuoteMaterialsForm() {
  const form = document.getElementById('quote-materials-form');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const quoteId = parseInt(route().parts[2], 10);
    const quote = getQuoteById(quoteId);
    if (!quote) return navigate('studio/cenove-nabidky');

    const fd = new FormData(form);
    quote.materials = fd.get('materials');
    quote.deadline = fd.get('deadline');
    upsertQuote(quote);

    let order = null;
    try {
      order = createEmptyOrder(quote.user_id);
      order.items = (quote.items || []).map((item, i) => ({
        ...item,
        item_id: `${order.order_id}-${i + 1}`,
        params: { ...(item.params || {}) },
      }));
      order.materials = quote.materials;
      order.deadline = quote.deadline;
      order.oznaceni = quote.oznaceni || '';
      odeslatObjednavku(order);
    } catch {
      if (order?.order_id != null) deleteOrder(order.order_id);
      return;
    }
    deleteQuote(quote.quote_id);
    navigate('potvrzeni');
  });
}

function bindProductForms() {
  const form = document.getElementById('product-form');
  if (!form) return;

  const type = form.dataset.type;
  form.querySelectorAll('input, select, textarea').forEach((el) => {
    el.addEventListener('input', () => updateProductPrice(type));
    el.addEventListener('change', () => {
      if (el.id === 'stuha') refreshRatioSelect(el.value);
      updateProductPrice(type);
    });
  });

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const orderId = parseInt(form.dataset.orderId, 10);
    const order = getOrder(orderId);
    const variables = getVariables();
    const input = readProductForm(type);
    const net = CALCULATORS[type](variables, input);
    const netVat = withVat(net, variables);
    const count = parseInt(input.count, 10) || 1;

    const item = {
      item_id: `${orderId}-${order.items.length + 1}`,
      type,
      count,
      params: { ...input },
      price_net: net,
      price_vat: netVat,
    };

    order.items.push(item);
    upsertOrder(order);
    setDraftOrder(order);
    navigate(`zakazka/${orderId}`);
  });
}

function refreshRatioSelect(stuhaKey) {
  const select = document.getElementById('ratio');
  if (!select) return;
  select.innerHTML = ratioOptions(stuhaKey, STUHY[stuhaKey]?.ratios[0]);
}

function renderSchemaPage(orderId, itemIdx) {
  const user = currentUser();
  const order = getOrder(orderId);
  const item = order?.items[parseInt(itemIdx, 10)];
  if (!item) return navigate(`zakazka/${orderId}`);

  app.innerHTML = shell(
    user,
    'Výrobní schéma',
    `
    ${renderSchema(item)}
    <div class="toolbar">
      <button type="button" class="btn btn-muted" onclick="window.print()">Tisk</button>
      <a href="#/zakazka/${orderId}" class="btn btn-primary">Zpět na zakázku</a>
    </div>`,
    { mode: 'customer' }
  );
}

function renderMaterials(orderId) {
  const user = currentUser();
  const order = getOrder(orderId);
  if (!order || !order.items.length) return navigate(`zakazka/${orderId}`);
  if (!user.admin && !jeOznaceniVyplnene(order)) {
    zakazkaOznaceniChyba = true;
    return navigate(`zakazka/${orderId}`);
  }

  app.innerHTML = shell(
    user,
    'Soupis materiálu',
    `
    <form id="materials-form" class="form form-narrow">
      <label for="materials-text">Materiály (látka, barva, dodavatel…)
        <textarea id="materials-text" name="materials" rows="6" required>${esc(order.materials || '')}</textarea>
      </label>
      <label for="deadline">Termín dodání
        <input type="date" id="deadline" name="deadline" value="${order.deadline || ''}" required>
      </label>
      <div class="toolbar">
        <a href="#/zakazka/${orderId}" class="btn btn-muted">Zpět</a>
        <button type="submit" class="btn btn-primary">Odeslat objednávku</button>
      </div>
    </form>`,
    { mode: 'customer' }
  );
}

function bindMaterialsForm() {
  const form = document.getElementById('materials-form');
  if (!form) return;
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const orderId = parseInt(route().parts[1], 10);
    const order = getOrder(orderId);
    const fd = new FormData(form);
    order.materials = fd.get('materials');
    order.deadline = fd.get('deadline');
    odeslatObjednavku(order);
    navigate('potvrzeni');
  });
}

function renderConfirmation() {
  const user = currentUser();
  app.innerHTML = shell(
    user,
    'Objednávka odeslána',
    `
    <div class="success-box">
      <p>Děkujeme. Vaše objednávka byla odeslána ke zpracování.</p>
      <a href="#/welcome" class="btn btn-primary">Zpět na přehled zakázek</a>
    </div>`,
    { mode: 'customer' }
  );
}

function getUserName(userId) {
  const u = getUsers().find((x) => x.user_id === userId);
  return u?.name || `#${userId}`;
}

function formatDate(iso) {
  if (!iso) return '—';
  return new Date(iso).toLocaleDateString('cs-CZ');
}

function esc(s) {
  return String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/"/g, '&quot;');
}

window.addEventListener('error', (event) => {
  const box = document.getElementById('app');
  if (!box) return;
  box.innerHTML = `<p class="form-error">Chyba aplikace: ${esc(event.message)}</p>`;
});

window.addEventListener('unhandledrejection', (event) => {
  const box = document.getElementById('app');
  if (!box) return;
  box.innerHTML = `<p class="form-error">Chyba aplikace: ${esc(event.reason)}</p>`;
});

start();

/** Odhlásí i Laravel relaci (CSRF vkládá B2bAppController) a přejde na /b2b. */
function odhlasitZLaravelu() {
  const token = window.__TNM_CSRF;
  fetch('/logout', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
      'X-CSRF-TOKEN': token,
      Accept: 'text/html',
    },
    body: `_token=${encodeURIComponent(token)}`,
    credentials: 'same-origin',
  }).finally(() => {
    location.href = '/b2b';
  });
}

function start() {
  initStorage();
  const user = currentUser();
  if (user && (!location.hash || location.hash === '#' || location.hash === '#/')) {
    navigate(user.admin ? 'admin' : 'welcome');
    return;
  }
  render();
}
