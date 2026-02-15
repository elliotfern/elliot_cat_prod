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
      case '&': return '&amp;';
      case '<': return '&lt;';
      case '>': return '&gt;';
      case '"': return '&quot;';
      case "'": return '&#039;';
      default: return c;
    }
  });
}

async function fetchMe(): Promise<MeResponse | null> {
  try {
    const res = await fetch(ME_ENDPOINT, {
      method: 'GET',
      credentials: 'include', // IMPORTANT: cookie token
      headers: { 'Accept': 'application/json' },
    });

    if (!res.ok) return null;
    return (await res.json()) as MeResponse;
  } catch {
    return null;
  }
}

function renderGuest(slot: HTMLElement): void {
  slot.innerHTML = `
    <a class="btn btn-outline-primary btn-sm" href="${GESTIO_URL}">
      Accés àrea usuari
    </a>
  `;
}

function renderAuthed(slot: HTMLElement, fullName: string): void {
  const safeName = escapeHtml(fullName);

  // Dropdown Bootstrap 5 (usa data-bs-toggle)
  slot.innerHTML = `
    <div class="dropdown">
      <button
        class="btn btn-outline-primary btn-sm dropdown-toggle"
        type="button"
        id="userAreaDropdown"
        data-bs-toggle="dropdown"
        aria-expanded="false">
        Accés àrea usuari
      </button>

      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userAreaDropdown">
        <li>
          <a class="dropdown-item d-flex flex-column" href="${GESTIO_URL}">
            <span class="small text-muted">Usuari</span>
            <span class="fw-semibold">${safeName}</span>
          </a>
        </li>
      </ul>
    </div>
  `;
}

/**
 * Llamar en cualquier página:
 * initUserAreaButton();
 */
export async function initUserAreaButton(): Promise<void> {
  const slot = document.getElementById('userAreaSlot');
  if (!slot) return;

  // Estado inicial rápido (opcional): invitado
  renderGuest(slot);

  const me = await fetchMe();
  if (me?.authenticated && me.full_name) {
    renderAuthed(slot, me.full_name);
  } else {
    renderGuest(slot);
  }
}
