import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Viatge } from '../../types/Viatge';

export async function taulaLlistatViatges() {
  const isAdmin = await getIsAdmin(); // Comprovar si és admin
  let gestioUrl: string = '';

  if (isAdmin) {
    gestioUrl = '/gestio';
  }

  const columns: TaulaDinamica<Viatge>[] = [
    {
      header: 'Viatge',
      field: 'viatge',
      render: (_: unknown, row: Viatge) => `<a href="https://${window.location.host}${gestioUrl}/viatges/fitxa-viatge/${row.slug}">${row.viatge}</a>`,
    },
    { header: 'Descripció', field: 'descripcio' },
    { header: 'País', field: 'pais_cat' },
    {
      header: 'Data',
      field: 'dataInici',
      render: (_: unknown, row: Viatge) => {
        const inici = formatData(row.dataInici);
        const fi = row.dataFi && row.dataFi !== '0' ? formatData(row.dataFi) : 'present';
        return `${inici} - ${fi}`;
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Viatge) => `
        <a href="https://${window.location.host}/gestio/viatges/modifica-viatge/${row.id}">
            <button class="btn-petit">Modifica</button>
        </a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/viatges/get/?llistatViatges`,
    containerId: 'taulaLlistatViatges',
    columns,
    filterKeys: ['viatge', 'descripcio'],
    filterByField: 'pais_cat',
  });
}
