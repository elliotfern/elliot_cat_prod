import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { Link } from '../../types/Link';
import { TaulaDinamica } from '../../types/TaulaDinamica';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatAdrecesPerTema() {
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
    {
      header: 'Enllaç',
      field: 'tema',
      render: (_: unknown, row: Link) => {
        // Verificamos si `row.tema` tiene un valor
        if (row.tema) {
          const nomTema = document.getElementById('nomTema');
          if (nomTema) {
            nomTema.innerHTML = `Tema: ${row.tema}`;
          }
        }

        // Generamos el enlace con los valores de `row.nom` y `row.url`
        return `<a id="${row.idTema}" title="Enllaç" href="${row.url}" target="_blank">${row.nom}</a>`;
      },
    },
    { header: 'Idioma', field: 'idioma_ca' },
    { header: 'Tipus', field: 'type_ca' },
    {
      header: 'Data creació',
      field: 'tema',
      render: (_: unknown, row: Link) => `${formatData(row.dateCreated)}`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Link) => `<a id="${row.idTema}" title="Modifica" href="https://${window.location.hostname}${gestioUrl}/adreces/modifica-link/${row.linkId}"><button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/adreces/get/?type=topic&id=${slug}`,
    containerId: 'taulaLlistatAdreces',
    columns,
    filterKeys: ['nom'],
    filterByField: 'type_ca',
  });
}
