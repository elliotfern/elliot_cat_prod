import { getIsAdmin } from '../../../services/auth/isAdmin';
import { PersonaView } from '../../../types/PersonaView';

const mesosCatalans = ['gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

function formatData(dia: number | null, mes: number | null, any: number | null): string {
  if (dia && mes && any) {
    return `${dia} ${mesosCatalans[mes - 1]} ${any}`;
  }

  if (mes && any) {
    return `${mesosCatalans[mes - 1]} ${any}`;
  }

  if (any) {
    return String(any);
  }

  return '';
}

export async function renderEsdeveniments(persona: PersonaView): Promise<HTMLElement | null> {
  if (!Array.isArray(persona.esdeveniments) || persona.esdeveniments.length === 0) {
    return null;
  }

  const isAdmin = await getIsAdmin();

  const wrapper = document.createElement('div');
  wrapper.className = 'mb-4';

  wrapper.innerHTML = `
    <h4 class="mb-3">Participació en esdeveniments històrics</h4>

    <div class="table-responsive">
      <table class="table table-sm table-striped table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Esdeveniment</th>
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

  for (const esdeveniment of persona.esdeveniments) {
    const tr = document.createElement('tr');

    const tdNom = document.createElement('td');

    const linkEsdeveniment = document.createElement('a');

    linkEsdeveniment.href = isAdmin ? `/gestio/historia/fitxa-esdeveniment/${encodeURIComponent(esdeveniment.slug)}` : `/banc-dades/esdeveniments/${encodeURIComponent(esdeveniment.slug)}`;

    linkEsdeveniment.textContent = esdeveniment.nom;

    tdNom.appendChild(linkEsdeveniment);

    const tdPeriode = document.createElement('td');

    const inici = formatData(esdeveniment.data_inici_dia, esdeveniment.data_inici_mes, esdeveniment.data_inici_any);

    const final = formatData(esdeveniment.data_final_dia, esdeveniment.data_final_mes, esdeveniment.data_final_any);

    if (inici && final) {
      tdPeriode.textContent = `${inici} – ${final}`;
    } else if (inici) {
      tdPeriode.textContent = `Des de ${inici}`;
    } else if (final) {
      tdPeriode.textContent = `Fins a ${final}`;
    }

    tr.append(tdNom, tdPeriode);

    // Accions: només administradors
    if (isAdmin) {
      const tdAccions = document.createElement('td');
      tdAccions.className = 'text-end';

      const editLink = document.createElement('a');
      editLink.href = `/gestio/historia/modifica-esdeveniment/${encodeURIComponent(esdeveniment.id)}`;
      editLink.className = 'btn btn-sm btn-outline-warning';
      editLink.textContent = 'Modifica';

      tdAccions.appendChild(editLink);
      tr.appendChild(tdAccions);
    }

    tbody.appendChild(tr);
  }

  return wrapper;
}
