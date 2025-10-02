import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { DOMAIN_WEB } from '../../utils/urls';
import { SubTema } from '../../types/SubTema';

export async function taulaLlistatTemaId(id: string) {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<SubTema>[] = [
    {
      header: 'Subtema',
      field: 'tema_ca',
      render: (_: unknown, row: SubTema) => `<a id="${row.id}" href="${DOMAIN_WEB}/gestio/adreces/llistat-subtema/${row.id}">${row.sub_tema_ca}</a>`,
    },
  ];

  columns.push({
    header: 'Accions',
    field: 'id',
    render: (_: unknown, row: SubTema) => `<a id="${row.id}" title="Modifica" href="${DOMAIN_WEB}/gestio/adreces/modifica-tema/${row.id}"><button type="button" class="button btn-petit">Modifica</button></a>`,
  });

  renderDynamicTable({
    url: `https://${window.location.host}/api/adreces/get/llistatLinksTemaId?id=${id}`,
    containerId: 'taulaLlistatTemaId',
    columns,
    filterKeys: ['tema_ca'],
    //filterByField: 'tema_ca',
  });
}
