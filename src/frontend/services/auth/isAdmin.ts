// src/frontend/services/auth/isAdmin.ts
// Solo UX: pinta botones. Seguridad real: backend (403).

type MeResponse = {
  authenticated: boolean;
  user_id?: string | null;
  email?: string | null;
  full_name?: string | null;
  user_type?: number | null;
  is_admin?: boolean;
  error?: string;
};

type CachedMe = {
  me: MeResponse;
  expiry: number; // ms epoch
  fingerprint: string; // uid:<user_id> o anon
};

const ME_CACHE_KEY = 'auth.me.v1';
const DEFAULT_TTL_MS = 10 * 60 * 1000;

function safeJsonParse<T>(raw: string): T | null {
  try {
    return JSON.parse(raw) as T;
  } catch {
    return null;
  }
}

function buildFingerprint(me: MeResponse): string {
  const uid = String(me.user_id ?? '').trim();
  return uid ? `uid:${uid}` : 'anon';
}

function isAdminFromMe(me: MeResponse): boolean {
  return !!me.authenticated && (me.user_type === 1 || me.is_admin === true);
}

function clearMeCache(): void {
  localStorage.removeItem(ME_CACHE_KEY);
}

export async function fetchMe(apiBase = 'https://elliot.cat'): Promise<MeResponse> {
  const base = apiBase.replace(/\/+$/, '');
  const url = `${base}/api/auth/get/?me`;

  let res: Response;
  try {
    res = await fetch(url, {
      method: 'GET',
      credentials: 'include',
      headers: { Accept: 'application/json' },
      cache: 'no-store',
    });
  } catch {
    return { authenticated: false, error: 'NETWORK_ERROR' };
  }

  if (res.status === 401) return { authenticated: false };
  if (!res.ok) return { authenticated: false, error: `HTTP_${res.status}` };

  try {
    const data = (await res.json()) as MeResponse;
    if (!data || typeof data.authenticated !== 'boolean') {
      return { authenticated: false, error: 'BAD_JSON_SHAPE' };
    }
    return data;
  } catch {
    return { authenticated: false, error: 'BAD_JSON' };
  }
}

/**
 * /me con cache (UX).
 * - TTL corto
 * - sin doble request si cache válido
 * - cache atado a user_id para no mezclar usuarios
 */
export async function getMeCached(apiBase?: string, opts?: { ttlMs?: number; forceRefresh?: boolean }): Promise<MeResponse> {
  const ttlMs = opts?.ttlMs ?? DEFAULT_TTL_MS;
  const forceRefresh = opts?.forceRefresh ?? false;
  const now = Date.now();

  if (!forceRefresh) {
    const raw = localStorage.getItem(ME_CACHE_KEY);
    if (raw) {
      const cached = safeJsonParse<CachedMe>(raw);

      if (cached?.me && typeof cached.expiry === 'number' && cached.expiry > now) {
        // Cache válido → devolvemos sin llamar a red
        return cached.me;
      }

      // expirado o corrupto
      clearMeCache();
    }
  }

  // Refresh real
  const me = await fetchMe(apiBase);

  // Si no autenticado → limpiar para evitar UI “fantasma”
  if (!me.authenticated) {
    clearMeCache();
    return me;
  }

  const payload: CachedMe = {
    me,
    fingerprint: buildFingerprint(me),
    expiry: now + ttlMs,
  };

  try {
    localStorage.setItem(ME_CACHE_KEY, JSON.stringify(payload));
  } catch {
    // Si falla el storage, seguimos sin cache. UX OK igualmente.
  }

  return me;
}

export async function getIsAdmin(apiBase?: string, opts?: { ttlMs?: number; forceRefresh?: boolean }): Promise<boolean> {
  const me = await getMeCached(apiBase, opts);
  return isAdminFromMe(me);
}

export async function isAdminUser(apiBase?: string): Promise<boolean> {
  const me = await fetchMe(apiBase);
  return isAdminFromMe(me);
}

/** Limpia cache en logout */
export function clearAuthCache(): void {
  clearMeCache();
}

/**
 * Llama a esto cuando una request protegida devuelva 401/403.
 * - 401: sesión expirada → limpiar
 * - 403: permisos cambiaron (o cache mentía) → limpiar para repintar UI
 */
export function handleAuthErrorStatus(status: number): void {
  if (status === 401 || status === 403) clearMeCache();
}
