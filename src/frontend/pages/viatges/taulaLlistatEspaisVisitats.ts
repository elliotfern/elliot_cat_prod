import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Espai } from '../../types/Espai';

export async function taulaLlistatEspaisVisitats() {
  const isAdmin = await getIsAdmin(); // Comprovar si és admin

  const columns: TaulaDinamica<Espai>[] = [
    {
      header: 'Espai',
      field: 'nom',
      render: (_: unknown, row: Espai) => `<a href="https://${window.location.host}/gestio/viatges/fitxa-espai/${row.id}">${row.nom}</a>`,
    },
    {
      header: 'Viatge',
      field: 'viatge',
      render: (_: unknown, row: Espai) => `<a href="https://${window.location.host}/gestio/viatges/fitxa-viatge/${row.viatgeSlug}">${row.viatge}</a>`,
    },

    { header: 'Data', field: 'dataVisita' },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Espai) => `
        <a href="https://${window.location.host}/gestio/viatges/modifica-espai-visitat/${row.id}">
            <button class="btn-petit">Modifica</button>
        </a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/viatges/get/llistatEspaisVisitats`,
    containerId: 'taulaLlistatEspaisVisitats',
    columns,
    filterKeys: ['nom'],
    filterByField: 'viatge',
  });
}
