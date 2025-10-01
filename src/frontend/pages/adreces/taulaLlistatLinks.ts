import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Link } from '../../types/Link';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatLinks() {
  const isAdmin = await getIsAdmin();
  let slug: string = '';
  let gestioUrl: string = '';

  if (isAdmin) {
    slug = pageType[3];
    gestioUrl = '/gestio';
  } else {
    slug = pageType[2];
  }

  const columns: TaulaDinamica<Link>[] = [
    // SELECT uuid_bin_to_text(l.id) AS id, l.nom, l.web, l.dateCreated, l.dateModified, st.tema_ca, s.sub_tema_ca, t.tipus_ca, i.idioma_ca
    {
      header: 'Enllaç',
      field: 'nom',
      render: (_: unknown, row: Link) => `<a id="${row.id}" href="${row.web}" target="_blank">${row.nom}</a>`,
    },
    { header: 'Categoría', field: 'tema_ca' },

    { header: 'Tema', field: 'sub_tema_ca' },

    { header: 'Tipus', field: 'tipus_ca' },

    { header: 'Última actualització', field: 'dateModified', render: (_: unknown, row: Link) => formatData(row.dateModified) },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Link) => `<a id="${row.id}" title="Show movie details" href="https://${window.location.hostname}${gestioUrl}/adreces/modifica-link/${row.id}"><button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/adreces/get/llistatLinks`,
    containerId: 'taulaLlistatLinks',
    columns,
    filterKeys: ['nom'],
    filterByField: 'tema_ca',
  });
}
