import { getIsAdmin } from '../../../services/auth/isAdmin';
import { PersonaView } from '../../../types/PersonaView';

export async function renderCarrecs(persona: PersonaView): Promise<HTMLElement | null> {
  if (!Array.isArray(persona.carrecs) || persona.carrecs.length === 0) {
    return null;
  }

  const isAdmin = await getIsAdmin();

  const wrapper = document.createElement('div');
  wrapper.className = 'mb-4';

  wrapper.innerHTML = `
    <h4 class="mb-3">Càrrecs i responsabilitats</h4>

    <div class="table-responsive">
      <table class="table table-sm table-striped table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Càrrec</th>
            <th>Organització/Institució</th>
            <th>Període</th>
            ${isAdmin ? '<th class="text-end">Accions</th>' : ''}
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  `;

  const tbody = wrapper.querySelector('tbody');

  if (!tbody) return null;

  for (const carrec of persona.carrecs) {
    const tr = document.createElement('tr');

    const tdNom = document.createElement('td');
    tdNom.textContent = carrec.nom;

    const tdOrganitzacio = document.createElement('td');
    tdOrganitzacio.textContent = carrec.organitzacio ?? '';

    const tdPeriode = document.createElement('td');

    const inici = carrec.inici;
    const fi = carrec.fi;

    if (inici !== null && fi !== null) {
      tdPeriode.textContent = `${inici}–${fi}`;
    } else if (inici !== null) {
      tdPeriode.textContent = `Des de ${inici}`;
    } else if (fi !== null) {
      tdPeriode.textContent = `Fins a ${fi}`;
    }

    tr.append(tdNom, tdOrganitzacio, tdPeriode);

    // Accions: només administradors
    if (isAdmin) {
      const tdAccions = document.createElement('td');
      tdAccions.className = 'text-end';

      const editLink = document.createElement('a');
      editLink.href = `/gestio/historia/modifica-carrec/${encodeURIComponent(carrec.id)}`;
      editLink.className = 'btn btn-sm btn-outline-warning';
      editLink.textContent = 'Modifica';

      tdAccions.appendChild(editLink);
      tr.appendChild(tdAccions);
    }

    tbody.appendChild(tr);
  }

  return wrapper;
}
