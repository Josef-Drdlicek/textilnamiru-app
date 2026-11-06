import { getUsers, getSession, setSession } from './storage.js';

export function login(loginName, password) {
  const user = getUsers().find(
    (u) => u.login === loginName && u.password === password && u.active !== false
  );
  if (!user) return { ok: false, error: 'Neplatné přihlašovací údaje.' };
  const { password: _, ...safeUser } = user;
  setSession({ user: safeUser, loggedAt: Date.now() });
  return { ok: true, user: safeUser };
}

export function logout() {
  setSession(null);
}

export function currentUser() {
  const session = getSession();
  return session?.user ?? null;
}

export function requireAuth(isAdmin = null) {
  const user = currentUser();
  if (!user) return { ok: false, redirect: 'login' };
  if (isAdmin === true && !user.admin) return { ok: false, redirect: 'welcome' };
  if (isAdmin === false && user.admin) return { ok: false, redirect: 'admin' };
  return { ok: true, user };
}
