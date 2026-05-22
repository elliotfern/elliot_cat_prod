// src/frontend/services/auth/isAdmin.ts

import { api } from '../../core/api/client';

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
  expiry: number;
  fingerprint: string;
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

export async function fetchMe(): Promise<MeResponse> {
  try {
    return await api.get<MeResponse>('auth/get/?me');
  } catch (error) {
    console.error(error);

    return {
      authenticated: false,
      error: 'AUTH_ERROR',
    };
  }
}

/**
 * /me con cache UX
 */
export async function getMeCached(opts?: { ttlMs?: number; forceRefresh?: boolean }): Promise<MeResponse> {
  const ttlMs = opts?.ttlMs ?? DEFAULT_TTL_MS;
  const forceRefresh = opts?.forceRefresh ?? false;

  const now = Date.now();

  if (!forceRefresh) {
    const raw = localStorage.getItem(ME_CACHE_KEY);

    if (raw) {
      const cached = safeJsonParse<CachedMe>(raw);

      if (cached?.me && typeof cached.expiry === 'number' && cached.expiry > now) {
        return cached.me;
      }

      clearMeCache();
    }
  }

  const me = await fetchMe();

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
    // ignore
  }

  return me;
}

export async function getIsAdmin(opts?: { ttlMs?: number; forceRefresh?: boolean }): Promise<boolean> {
  const me = await getMeCached(opts);

  return isAdminFromMe(me);
}

export async function isAdminUser(): Promise<boolean> {
  const me = await fetchMe();

  return isAdminFromMe(me);
}

export function clearAuthCache(): void {
  clearMeCache();
}

export function handleAuthErrorStatus(status: number): void {
  if (status === 401 || status === 403) {
    clearMeCache();
  }
}
