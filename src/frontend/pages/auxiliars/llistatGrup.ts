import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';

export interface GrupPersones {
  id: string; // uuid string
  grup_ca: string;
}

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatGrupsPersones(): Promise<void> {
  const isAdmin = await getIsAdmin();

  const gestioUrl = isAdmin ? '/gestio' : '';

  const editHref = (id: string): string => `https://${window.location.hostname}${gestioUrl}/auxiliars/modifica-grup/${id}`;

  const columns: TaulaDinamica<GrupPersones>[] = [
    {
      header: 'Grup/professió (català)',
      field: 'grup_ca',
      render: (_: unknown, row: GrupPersones) => `${row.grup_ca}`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: GrupPersones) =>
        `<a title="Modifica" href="${editHref(row.id)}">
           <button class="btn-petit">Modifica</button>
         </a>`,
    });
  }

  renderDynamicTable({
    url: `https://elliot.cat/api/persones/get/?grupPersones`,
    containerId: 'taulaLlistatGrupsPersones',
    columns,
    filterKeys: ['grup_ca'],
  });
}
