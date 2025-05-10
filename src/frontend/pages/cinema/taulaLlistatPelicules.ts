import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';

interface EspaiRow {
  slug: string;
  pelicula: string;
  dataVisita: string;
  id: number;
  cognoms: string;
  nom: string;
}

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatPelicules() {
  const isAdmin = await getIsAdmin();
  let slug: string = '';
  let gestioUrl: string = '';

  if (isAdmin) {
    slug = pageType[3];
    gestioUrl = '/gestio';
  } else {
    slug = pageType[2];
  }

  const columns = [
    {
      header: 'Pel·lícula',
      field: 'pelicula',
      render: (_: unknown, row: EspaiRow) => `<a id="${row.id}" title="Show movie details" href="https://${window.location.hostname}${gestioUrl}/cinema/fitxa-pelicula/${row.slug}">${row.pelicula}</a>`,
    },
    { header: 'Any', field: 'any' },
    {
      header: 'Director/a',
      field: 'cognoms',
      render: (_: unknown, row: EspaiRow) => {
        return `${row.nom} ${row.cognoms}`;
      },
    },
    { header: 'País', field: 'pais_cat' },
    { header: 'Gènere', field: 'genere_ca' },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: EspaiRow) => `<a a id="${row.id}" title="Show movie details" href="https://${window.location.hostname}${gestioUrl}/cinema/modifica-pelicula/${row.slug}">Modifica</a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/cinema/get/?pelicules`,
    containerId: 'taulaLlistatPelicules',
    columns,
    filterKeys: ['nom', 'cognoms', 'pelicula'],
    filterByField: 'pais_cat',
  });
}
