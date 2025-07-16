import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Persona } from '../../types/Persona';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatPersones() {
  const isAdmin = await getIsAdmin();
  let slug: string = '';
  let gestioUrl: string = '';

  if (isAdmin) {
    slug = pageType[3];
    gestioUrl = '/gestio';
  } else {
    slug = pageType[2];
  }

  const columns: TaulaDinamica<Persona>[] = [
    {
      header: '',
      field: 'nameImg',
      render: (_: unknown, row: Persona) => {
        const detailUrl = `https://${window.location.host}${gestioUrl}/base-dades-persones/fitxa-persona/${row.slug}`;
        const fullImgUrl = `https://media.elliot.cat/img/persona/${row.nameImg}.jpg`;

        // Genera el enlace dinámico con la imagen
        return `<a id="${row.id}" title="Persona" href="${detailUrl}">
              <img src="${fullImgUrl}" style="height:70px">
            </a>`;
      },
    },
    {
      header: 'Nom i cognoms',
      field: 'nom',
      render: (_: unknown, row: Persona) => {
        // Genera el enlace dinámico sin la imagen
        return `<a id="${row.id}" title="${row.nom} ${row.cognoms}" 
               href="https://${window.location.hostname}${gestioUrl}/base-dades-persones/fitxa-persona/${row.slug}">
               ${row.nom} ${row.cognoms}
            </a>`;
      },
    },
    { header: 'País', field: 'pais_cat' },
    { header: 'Grup', field: 'grup' },
    {
      header: 'Anys',
      field: 'yearBorn',
      render: (_: unknown, row: Persona) => `${row.yearDie ? `${row.yearBorn} - ${row.yearDie}` : row.yearBorn}`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Persona) => `<a id="${row.id}" title="Modifica" href="https://${window.location.hostname}${gestioUrl}/base-dades-persones/modifica-persona/${row.slug}"><button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/persones/get/?type=llistatPersones`,
    containerId: 'taulaLlistatPersones',
    columns,
    filterKeys: ['nom', 'cognoms'],
    filterByField: 'grup',
  });
}
