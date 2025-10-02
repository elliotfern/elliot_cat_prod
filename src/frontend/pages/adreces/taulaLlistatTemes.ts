import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Tema } from '../../types/Tema';
import { DOMAIN_WEB } from '../../utils/urls';

export async function taulaLlistatTemes() {
  const columns: TaulaDinamica<Tema>[] = [
    {
      header: 'Tema',
      field: 'tema_ca',
      render: (_: unknown, row: Tema) => `<a id="${row.id}" href="${DOMAIN_WEB}/gestio/adreces/llistat-tema/${row.id}">${row.tema_ca}</a>`,
    },
  ];

  columns.push({
    header: 'Accions',
    field: 'id',
    render: (_: unknown, row: Tema) => `<a id="${row.id}" title="Modifica" href="${DOMAIN_WEB}/gestio/adreces/modifica-tema/${row.id}"><button type="button" class="button btn-petit">Modifica</button></a>`,
  });

  renderDynamicTable({
    url: `https://${window.location.host}/api/adreces/get/llistatTemes`,
    containerId: 'taulaLlistatTemes',
    columns,
    filterKeys: ['tema_ca'],
    filterByField: 'tema_ca',
  });
}
