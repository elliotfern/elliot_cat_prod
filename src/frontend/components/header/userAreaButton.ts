type MeResponse = {
  authenticated: boolean;
  user_id: number | string | null;
  email: string | null;
  full_name: string | null;
  user_type: number | null;
  is_admin: boolean;
};

const ME_ENDPOINT = '/api/auth/get/?me';
const GESTIO_URL = 'https://elliot.cat/gestio';

function escapeHtml(s: string): string {
  return s.replace(/[&<>"']/g, (c) => {
    switch (c) {
      case '&':
        return '&amp;';
      case '<':
        return '&lt;';
      case '>':
        return '&gt;';
      case '"':
        return '&quot;';
      case "'":
        return '&#039;';
      default:
        return c;
    }
  });
}

async function fetchMe(): Promise<MeResponse | null> {
  try {
    const res = await fetch(ME_ENDPOINT, {
      method: 'GET',
      credentials: 'include',
      headers: { Accept: 'application/json' },
    });
    if (!res.ok) return null;
    return (await res.json()) as MeResponse;
  } catch {
    return null;
  }
}

function renderUserLink(slot: HTMLElement, label: string, isAuthenticated: boolean): void {
  const safeLabel = escapeHtml(label);

  const btnClass = isAuthenticated ? 'btn btn-primary btn-sm fw-semibold shadow-sm' : 'btn btn-outline-primary btn-sm';

  slot.innerHTML = `
    <a class="${btnClass}" href="${GESTIO_URL}" id="userAreaBtn">
      ${safeLabel}
    </a>
  `;
}

export async function initUserAreaButton(): Promise<void> {
  const slot = document.getElementById('userAreaSlot');
  if (!slot) return;

  // Estado inicial rápido
  renderUserLink(slot, 'Accés àrea usuari', false);

  const me = await fetchMe();

  if (me?.authenticated) {
    const name = (me.full_name ?? '').trim();
    renderUserLink(slot, name !== '' ? name : 'Àrea usuari', true);
  } else {
    renderUserLink(slot, 'Accés àrea usuari', false);
  }
}
