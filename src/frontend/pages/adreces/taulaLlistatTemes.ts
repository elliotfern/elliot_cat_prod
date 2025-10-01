import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getPageType } from '../../utils/urlPath';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Tema } from '../../types/Tema';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatTemes() {
  const columns: TaulaDinamica<Tema>[] = [
    {
      header: 'Tema',
      field: 'tema_ca',
      render: (_: unknown, row: Tema) => `${row.tema_ca}`,
    },
  ];

  columns.push({
    header: 'Accions',
    field: 'id',
    render: (_: unknown, row: Tema) => `<a id="${row.id}" title="Modifica" href="https://${window.location.hostname}/gestio/adreces/modifica-tema/${row.id}"><button type="button" class="button btn-petit">Modifica</button></a>`,
  });

  renderDynamicTable({
    url: `https://${window.location.host}/api/adreces/get/llistatTemes`,
    containerId: 'taulaLlistatTemes',
    columns,
    filterKeys: ['tema_ca'],
    filterByField: 'tema_ca',
  });
}
