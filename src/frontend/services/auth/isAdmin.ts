type MeResponse = {
  authenticated: boolean;
  user_id?: string | null;
  email?: string | null;
  full_name?: string | null;
  user_type?: number | null;
  is_admin?: boolean;
  error?: string;
};

type Cached<T> = {
  value: T;
  expiry: number;
  userId?: string | null;
};

// 1) Llama al endpoint /me
export async function fetchMe(apiBase = 'https://elliot.cat'): Promise<MeResponse> {
  const url = `${apiBase}/api/auth/get/?me=1`;

  const res = await fetch(url, {
    method: 'GET',
    credentials: 'include',
    headers: { Accept: 'application/json' },
  });

  if (res.status === 401) return { authenticated: false };
  if (!res.ok) return { authenticated: false, error: `HTTP ${res.status}` };

  return (await res.json()) as MeResponse;
}

// 2) Calcula si es admin desde /me
export async function isAdminUser(apiBase?: string): Promise<boolean> {
  try {
    const me = await fetchMe(apiBase);
    return !!me.authenticated && (me.user_type === 1 || me.is_admin === true);
  } catch (e) {
    console.error('Error al verificar admin:', e);
    return false;
  }
}

// 3) Cache 30 min (y evita mezclar usuarios: guarda también user_id)
export async function getIsAdmin(apiBase?: string): Promise<boolean> {
  const key = 'isAdmin';
  const item = localStorage.getItem(key);

  if (item) {
    try {
      const parsed = JSON.parse(item) as Cached<boolean>;
      const now = Date.now();

      if (parsed.expiry > now) {
        // Si tenemos userId cacheado, lo validamos contra el actual
        // (si no quieres 2 llamadas, puedes quitar esto; pero así es más correcto)
        const me = await fetchMe(apiBase);
        if (!me.authenticated) {
          localStorage.removeItem(key);
          return false;
        }

        if (parsed.userId && me.user_id && parsed.userId !== me.user_id) {
          // Usuario distinto -> invalida cache
          localStorage.removeItem(key);
        } else {
          return parsed.value;
        }
      } else {
        localStorage.removeItem(key);
      }
    } catch (e) {
      console.error('Valor de isAdmin corrupto:', e);
      localStorage.removeItem(key);
    }
  }

  // Si no hay valor o está expirado, pedir a la API
  const me = await fetchMe(apiBase);
  const value = !!me.authenticated && (me.user_type === 1 || me.is_admin === true);

  localStorage.setItem(
    key,
    JSON.stringify({
      value,
      userId: me.user_id ?? null,
      expiry: Date.now() + 30 * 60 * 1000,
    } satisfies Cached<boolean>)
  );

  return value;
}

// 4) Útil: limpiar cache cuando haces logout
export function clearAuthCache(): void {
  localStorage.removeItem('isAdmin');
}
