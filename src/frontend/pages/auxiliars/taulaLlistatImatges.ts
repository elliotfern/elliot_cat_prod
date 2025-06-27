import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Imatge } from '../../types/Imatge';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatImatges() {
  const isAdmin = await getIsAdmin();
  let slug: string = '';
  let gestioUrl: string = '';

  if (isAdmin) {
    slug = pageType[3];
    gestioUrl = '/gestio';
  } else {
    slug = pageType[2];
  }

  const columns: TaulaDinamica<Imatge>[] = [
    {
      header: '',
      field: 'nameImg',
      render: (_: unknown, row: Imatge) => `<a id="${row.id}" title="Imatges detalls" href="https://${window.location.hostname}${gestioUrl}/auxiliars/fitxa-imatge/${row.id}"> <img src="https://media.elliot.cat/img/${row.name}/${row.nameImg}.jpg" alt="${row.nom}" width="60" height="auto"> </a>`,
    },
    {
      header: 'Imatge',
      field: 'nom',
      render: (_: unknown, row: Imatge) => `<a id="${row.id}" title="Imatges detalls" href="https://${window.location.hostname}${gestioUrl}/auxiliars/fitxa-imatge/${row.id}">${row.nom}</a>`,
    },
    { header: 'Tipus Imatge', field: 'name' },
    {
      header: 'Data creació',
      field: 'dateCreated',
      render: (_: unknown, row: Imatge) => {
        return `${formatData(row.dateCreated)}`;
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Imatge) => `<a a id="${row.id}" title="Modifica" href="https://${window.location.hostname}${gestioUrl}/auxiliars/modifica-imatge/${row.id}"><button class="btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/auxiliars/get/?llistatCompletImatges`,
    containerId: 'taulaLlistatImatges',
    columns,
    filterKeys: ['nom'],
    filterByField: 'name',
  });
}
