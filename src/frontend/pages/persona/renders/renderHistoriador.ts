import { api } from '../../../Infrastructure/Api/Client/ApiClient';
import { getIsAdmin } from '../../../services/auth/isAdmin';
import { PersonaView } from '../../../types/PersonaView';

type LlibreAutor = {
  slug: string;
  titol: string;
  any?: number | null;
  id: string;
};

export async function renderHistoriador(persona: PersonaView): Promise<HTMLElement | null> {
  let llibres: LlibreAutor[];

  try {
    llibres = await api.get<LlibreAutor[]>('biblioteca/get/autorLlibres', {
      id: persona.id,
    });
  } catch (error) {
    console.error(error);
    return null;
  }

  if (!Array.isArray(llibres) || llibres.length === 0) {
    return null;
  }

  const isAdmin = await getIsAdmin();

  const wrapper = document.createElement('div');

  wrapper.innerHTML = `
    <h4 class="mb-3">📚 Llibres publicats</h4>

    <div class="table-responsive">
      <table class="table table-sm table-striped table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Títol</th>
            <th>Any</th>
            ${isAdmin ? '<th class="text-end">Accions</th>' : ''}
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  `;

  const tbody = wrapper.querySelector('tbody');

  if (!tbody) {
    return null;
  }

  for (const llibre of llibres) {
    const tr = document.createElement('tr');

    // Títol i enllaç
    const tdTitol = document.createElement('td');
    const linkLlibre = document.createElement('a');

    linkLlibre.href = isAdmin ? `/gestio/biblioteca/fitxa-llibre/${encodeURIComponent(llibre.slug)}` : `/biblioteca/fitxa-llibre/${encodeURIComponent(llibre.slug)}`;

    linkLlibre.textContent = llibre.titol;
    tdTitol.appendChild(linkLlibre);

    // Any de publicació
    const tdAny = document.createElement('td');
    tdAny.textContent = llibre.any != null ? String(llibre.any) : '';

    tr.append(tdTitol, tdAny);

    // Accions: només administradors
    if (isAdmin) {
      const tdAccions = document.createElement('td');
      tdAccions.className = 'text-end';

      const editLink = document.createElement('a');
      editLink.href = `/gestio/biblioteca/modifica-llibre/${encodeURIComponent(llibre.id)}`;
      editLink.className = 'btn btn-sm btn-outline-warning';
      editLink.textContent = 'Modifica';

      tdAccions.appendChild(editLink);
      tr.appendChild(tdAccions);
    }

    tbody.appendChild(tr);
  }

  return wrapper;
}
