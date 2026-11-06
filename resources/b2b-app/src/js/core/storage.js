import { DEFAULT_VARIABLES, DEMO_USERS, STUHY } from '../config/defaults.js';

const KEYS = {
  variables: 'tnm_variables',
  stuhy: 'tnm_stuhy',
  users: 'tnm_users',
  orders: 'tnm_orders',
  quotes: 'tnm_quotes',
  session: 'tnm_session',
  draftOrder: 'tnm_draft_order',
};

function read(key, fallback) {
  try {
    const raw = localStorage.getItem(key);
    return raw ? JSON.parse(raw) : fallback;
  } catch {
    return fallback;
  }
}

function write(key, value) {
  localStorage.setItem(key, JSON.stringify(value));
}

/** Do uloženého ceníku doplní chybějící výchozí kódy, ceny uživatele nepřepisuje. */
function withMissingVariables(stored) {
  const list = Array.isArray(stored) ? [...stored] : [];
  const codes = new Set(list.map((v) => v.code));
  for (const def of DEFAULT_VARIABLES) {
    if (!codes.has(def.code)) {
      list.push({ ...def });
      codes.add(def.code);
    }
  }
  return list;
}

export function initStorage() {
  const raw = localStorage.getItem(KEYS.variables);
  if (!raw) {
    write(KEYS.variables, DEFAULT_VARIABLES);
  } else {
    const stored = read(KEYS.variables, DEFAULT_VARIABLES);
    const merged = withMissingVariables(stored);
    if (merged.length !== stored.length) {
      write(KEYS.variables, merged);
    }
  }
  const rawStuhy = localStorage.getItem(KEYS.stuhy);
  if (!rawStuhy) {
    write(KEYS.stuhy, serializovatStuhy(STUHY));
  } else {
    const stored = read(KEYS.stuhy, {});
    const merged = withMissingStuhy(stored);
    write(KEYS.stuhy, merged);
  }
  uplatnitUlozeneStuhy();
  if (!localStorage.getItem(KEYS.users)) {
    write(KEYS.users, DEMO_USERS);
  }
  if (!localStorage.getItem(KEYS.orders)) {
    write(KEYS.orders, []);
  }
  if (!localStorage.getItem(KEYS.quotes)) {
    write(KEYS.quotes, []);
  }
}

function serializovatStuhy(src) {
  const out = {};
  for (const [code, item] of Object.entries(src)) {
    out[code] = {
      price: item.price,
      ratios: Array.isArray(item.ratios) ? [...item.ratios] : [],
    };
  }
  return out;
}

/** Chybějící kódy stuh doplní, uložené ceny a poměry nepřepisuje. */
function withMissingStuhy(stored) {
  const out = stored && typeof stored === 'object' ? { ...stored } : {};
  for (const [code, def] of Object.entries(serializovatStuhy(STUHY))) {
    if (!out[code]) out[code] = def;
  }
  return out;
}

/** Uložené ceny stuh zapíše do objektu STUHY, ať je kalkulace čte beze změny vzorců. */
function uplatnitUlozeneStuhy() {
  const stored = getStuhy();
  for (const code of Object.keys(STUHY)) {
    const s = stored[code];
    if (!s) continue;
    if (s.price != null && s.price !== '') STUHY[code].price = Number(s.price);
    if (Array.isArray(s.ratios)) STUHY[code].ratios = s.ratios.map(String);
  }
}

export function getStuhy() {
  return withMissingStuhy(read(KEYS.stuhy, serializovatStuhy(STUHY)));
}

export function saveStuhy(stuhy) {
  write(KEYS.stuhy, stuhy);
  uplatnitUlozeneStuhy();
}

export function getVariables() {
  return read(KEYS.variables, DEFAULT_VARIABLES);
}

export function saveVariables(variables) {
  write(KEYS.variables, variables);
}

export function getUsers() {
  return read(KEYS.users, DEMO_USERS);
}

export function saveUsers(users) {
  write(KEYS.users, users);
}

export function getUserById(id) {
  return getUsers().find((u) => u.user_id === id);
}

export function getOrders() {
  return read(KEYS.orders, []);
}

export function saveOrders(orders) {
  write(KEYS.orders, orders);
}

export function getOrderById(orderId) {
  return getOrders().find((o) => o.order_id === orderId);
}

export function upsertOrder(order) {
  const orders = getOrders();
  const idx = orders.findIndex((o) => o.order_id === order.order_id);
  if (idx >= 0) orders[idx] = order;
  else orders.push(order);
  saveOrders(orders);
  return order;
}

export function deleteOrder(orderId) {
  const predchozi = getOrders();
  const dalsi = predchozi.filter((o) => o.order_id !== orderId);
  const draft = getDraftOrder();
  const smazatDraft = Boolean(draft && draft.order_id === orderId);
  saveOrders(dalsi);
  try {
    if (smazatDraft) setDraftOrder(null);
  } catch (chyba) {
    saveOrders(predchozi);
    throw chyba;
  }
}

export function nextOrderId() {
  const orders = getOrders();
  return orders.length ? Math.max(...orders.map((o) => o.order_id)) + 1 : 5837;
}

export function getSession() {
  return read(KEYS.session, null);
}

export function setSession(session) {
  if (session) write(KEYS.session, session);
  else localStorage.removeItem(KEYS.session);
}

export function getQuotes() {
  return read(KEYS.quotes, []);
}

export function saveQuotes(quotes) {
  write(KEYS.quotes, quotes);
}

export function getQuoteById(quoteId) {
  return getQuotes().find((q) => q.quote_id === quoteId);
}

export function upsertQuote(quote) {
  const quotes = getQuotes();
  const idx = quotes.findIndex((q) => q.quote_id === quote.quote_id);
  if (idx >= 0) quotes[idx] = quote;
  else quotes.push(quote);
  saveQuotes(quotes);
  return quote;
}

export function deleteQuote(quoteId) {
  saveQuotes(getQuotes().filter((q) => q.quote_id !== quoteId));
}

export function nextQuoteId() {
  const quotes = getQuotes();
  return quotes.length ? Math.max(...quotes.map((q) => q.quote_id)) + 1 : 1;
}

export function getDraftOrder() {
  return read(KEYS.draftOrder, null);
}

export function setDraftOrder(order) {
  if (order) write(KEYS.draftOrder, order);
  else localStorage.removeItem(KEYS.draftOrder);
}
