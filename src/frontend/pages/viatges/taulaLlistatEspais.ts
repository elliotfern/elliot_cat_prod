import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Espai } from '../../types/Espai';

export async function taulaLlistatEspais() {
  const isAdmin = await getIsAdmin(); // Comprovar si és admin

  const columns: TaulaDinamica<Espai>[] = [
    {
      header: 'Espai',
      field: 'nom',
      render: (_: unknown, row: Espai) => `<a href="https://${window.location.host}/gestio/viatges/fitxa-espai/${row.slug}">${row.nom}</a>`,
    },
    { header: 'Ciutat', field: 'ciutat' },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Espai) => `
        <a href="https://${window.location.host}/gestio/viatges/modifica-espai/${row.id}">
            <button class="btn-petit">Modifica</button>
        </a>`,
    });
  }

  renderDynamicTable({
    url: `viatges/get/llistatEspais`,
    containerId: 'taulaLlistatEspais',
    columns,
    filterKeys: ['nom'],
    filterByField: 'ciutat',
  });
}
