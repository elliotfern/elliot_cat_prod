type ApiResponse<T> = {
  status: 'success' | 'error';
  message: string;
  errors: any;
  data: T;
};

type Autor = {
  // Ideal (relación autoincrement) para borrar seguro:
  rel_id?: number; // <-- AÑADE ESTO en tu GET si puedes

  // Datos del autor:
  id: string; // uuid del autor
  nom: string | null;
  cognoms: string | null;
  slug: string;
};

type LlibreMini = {
  id: string;
  slug: string;
  titol: string;
};

type LlibreAutorsPayload = {
  llibre: LlibreMini;
  autors: Autor[];
};

function escapeHtml(s: unknown): string {
  return String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function showErr(message: string) {
  const err = document.getElementById('missatgeErr');
  const ok = document.getElementById('missatgeOk');

  if (ok) {
    ok.style.display = 'none';
    ok.innerHTML = '';
  }

  if (!err) return;
  err.style.display = 'block';
  err.innerHTML = `<h4 class="alert-heading"><strong>${escapeHtml(message)}</strong></h4>`;
  err.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showOk(message: string) {
  const ok = document.getElementById('missatgeOk');
  const err = document.getElementById('missatgeErr');

  if (err) {
    err.style.display = 'none';
    err.innerHTML = '';
  }

  if (!ok) return;
  ok.style.display = 'block';
  ok.innerHTML = `<h4 class="alert-heading"><strong>${escapeHtml(message)}</strong></h4>`;
  ok.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function hideAlerts() {
  const ok = document.getElementById('missatgeOk');
  const err = document.getElementById('missatgeErr');
  if (ok) {
    ok.style.display = 'none';
    ok.innerHTML = '';
  }
  if (err) {
    err.style.display = 'none';
    err.innerHTML = '';
  }
}

function renderTable(llibreSlug: string, autors: Autor[]) {
  const wrap = document.getElementById('authorsTableWrap');
  if (!wrap) return;

  if (!autors.length) {
    wrap.innerHTML = `<p>No hi ha cap autor assignat a aquest llibre.</p>`;
    return;
  }

  const rows = autors
    .map((a) => {
      const full = [a.nom, a.cognoms].filter(Boolean).join(' ').trim() || a.slug;
      const href = `${window.location.origin}/gestio/biblioteca/fitxa-autor/${encodeURIComponent(a.slug)}`;

      // Para borrar: preferimos rel_id si existe; si no, fallback a autor uuid (menos ideal)
      const relIdAttr = a.rel_id != null ? `data-rel-id="${escapeHtml(a.rel_id)}"` : '';
      const autorIdAttr = `data-autor-id="${escapeHtml(a.id)}"`;
      const llibreAttr = `data-llibre-slug="${escapeHtml(llibreSlug)}"`;

      return `
        <tr>
          <td><a href="${href}">${escapeHtml(full)}</a></td>
          <td class="text-end">
            <button
              type="button"
              class="button btn-petit js-del-autor"
              ${relIdAttr}
              ${autorIdAttr}
              ${llibreAttr}
            >
              Elimina
            </button>
          </td>
        </tr>
      `;
    })
    .join('');

  wrap.innerHTML = `
    <div class="table-responsive">
      <table class="table table-striped">
        <thead class="table-primary">
          <tr>
            <th>Nom i cognoms</th>
            <th class="text-end">Elimina</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
  `;

  // Cablear deletes (delegación)
  bindDeleteHandlers(wrap);
}

function bindDeleteHandlers(container: HTMLElement) {
  // Evitar duplicar listeners si re-renderizas
  // (marcamos con una bandera)
  const anyContainer = container as any;
  if (anyContainer.__deleteBound) return;
  anyContainer.__deleteBound = true;

  container.addEventListener('click', async (ev) => {
    const target = ev.target as HTMLElement | null;
    if (!target) return;

    const btn = target.closest<HTMLButtonElement>('button.js-del-autor');
    if (!btn) return;

    const llibreSlug = btn.dataset.llibreSlug || '';
    const relIdStr = btn.dataset.relId; // preferido
    const autorId = btn.dataset.autorId; // fallback

    if (!llibreSlug) {
      showErr('Falta llibreSlug en el botó.');
      return;
    }

    // Preferimos relId
    if (relIdStr) {
      const relId = Number(relIdStr);
      if (!Number.isFinite(relId) || relId <= 0) {
        showErr('rel_id invàlid.');
        return;
      }
      await eliminarAutorPerRelacio(btn, relId, llibreSlug);
      return;
    }

    // Fallback: autor uuid (solo si NO tienes rel_id)
    if (!autorId) {
      showErr('Falta rel_id o autor_id per eliminar.');
      return;
    }

    // Si tu backend delete por autor_id necesita también llibre_slug o llibre_id,
    // aquí ya tenemos llibreSlug.
    await eliminarAutorPerAutorUuid(btn, autorId, llibreSlug);
  });
}

async function eliminarAutorPerRelacio(btn: HTMLButtonElement, relId: number, llibreSlug: string) {
  if (!confirm('Vols eliminar aquest autor del llibre?')) return;

  // lock doble click
  btn.disabled = true;
  const prevText = btn.textContent;
  btn.textContent = 'Eliminant...';
  hideAlerts();

  try {
    const res = await fetch(`/api/biblioteca/delete/?llibreAutorRel=${encodeURIComponent(String(relId))}`, {
      method: 'DELETE',
      headers: { Accept: 'application/json' },
    });

    const json = (await res.json().catch(() => null)) as any;

    if (!json) {
      showErr('Resposta invàlida del servidor');
      return;
    }

    if (json.status === 'success') {
      // opción A: recargar
      window.location.href = `${window.location.origin}/gestio/biblioteca/fitxa-llibre-autors/${encodeURIComponent(llibreSlug)}`;
      return;
    }

    showErr(json.message || 'Error eliminant autor');
  } catch (e) {
    console.error(e);
    showErr('Error de connexió amb el servidor');
  } finally {
    btn.disabled = false;
    btn.textContent = prevText ?? 'Elimina';
  }
}

// Fallback (menos ideal): delete pasando autor uuid + libro slug
// Necesita que crees endpoint en backend tipo:
// /api/biblioteca/delete/?llibreAutor&llibreSlug=...&autorId=...
async function eliminarAutorPerAutorUuid(btn: HTMLButtonElement, autorUuid: string, llibreSlug: string) {
  if (!confirm('Vols eliminar aquest autor del llibre?')) return;

  btn.disabled = true;
  const prevText = btn.textContent;
  btn.textContent = 'Eliminant...';
  hideAlerts();

  try {
    const res = await fetch(`/api/biblioteca/delete/?llibreAutor&llibreSlug=${encodeURIComponent(llibreSlug)}&autorId=${encodeURIComponent(autorUuid)}`, {
      method: 'DELETE',
      headers: { Accept: 'application/json' },
    });

    const json = (await res.json().catch(() => null)) as any;

    if (!json) {
      showErr('Resposta invàlida del servidor');
      return;
    }

    if (json.status === 'success') {
      window.location.href = `${window.location.origin}/gestio/biblioteca/fitxa-llibre-autors/${encodeURIComponent(llibreSlug)}`;
      return;
    }

    showErr(json.message || 'Error eliminant autor');
  } catch (e) {
    console.error(e);
    showErr('Error de connexió amb el servidor');
  } finally {
    btn.disabled = false;
    btn.textContent = prevText ?? 'Elimina';
  }
}

export async function initLlibreAutorsPage(llibreSlug: string) {
  try {
    hideAlerts();

    const res = await fetch(`/api/biblioteca/get/?type=llibreAutors&slug=${encodeURIComponent(llibreSlug)}`, {
      headers: { Accept: 'application/json' },
    });

    const json = (await res.json()) as ApiResponse<LlibreAutorsPayload>;

    if (!res.ok || json.status !== 'success') {
      showErr(json?.message || 'Error carregant autors del llibre');
      return;
    }

    const { llibre, autors } = json.data;

    const titleEl = document.getElementById('bookTitle');
    if (titleEl) titleEl.textContent = llibre?.titol ? `Llibre: ${llibre.titol}` : '';

    // Botón "Afegir autor" (mejor como <a> en HTML)
    const btn = document.getElementById('btnAfegirAutor') as HTMLAnchorElement | null;
    if (btn) {
      btn.style.display = 'inline-block';
      btn.href = `${window.location.origin}/gestio/biblioteca/llibre-autors-afegir/${encodeURIComponent(llibreSlug)}`;
    }

    renderTable(llibreSlug, autors || []);
  } catch (e) {
    showErr('Error de connexió amb el servidor');
    console.error(e);
  }
}

export function initAdminButtons(llibreSlug: string) {
  const adminWrap = document.getElementById('isAdminButton');
  const btnAfegir = document.getElementById('btnAfegirAutor');
  const btnTornar = document.getElementById('btnTornar');

  if (!adminWrap) return;

  // Mostrar bloque admin (ya está validado por PHP, esto es extra)
  adminWrap.style.display = 'block';

  if (btnAfegir) {
    // Si es <a>, mejor setear href y ya. Pero lo dejamos compatible:
    (btnAfegir as HTMLElement).addEventListener('click', (e) => {
      // Si ya tiene href como <a>, esto sobra.
      e.preventDefault();
      window.location.href = `${window.location.origin}/gestio/biblioteca/llibre-autors-afegir/${encodeURIComponent(llibreSlug)}`;
    });
  }

  if (btnTornar) {
    btnTornar.addEventListener('click', (e) => {
      e.preventDefault();
      history.back();
    });
  }
}
