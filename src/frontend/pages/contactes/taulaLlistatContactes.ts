import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatNaixementEdat } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Contacte } from '../../types/Contacte';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatContactes() {
  const isAdmin = await getIsAdmin();
  let slug: string = '';
  let gestioUrl: string = '';

  if (isAdmin) {
    slug = pageType[3];
    gestioUrl = '/gestio';
  } else {
    slug = pageType[2];
  }

  const columns: TaulaDinamica<Contacte>[] = [
    {
      header: 'Nom i cognoms',
      field: 'cognoms',
      render: (_: unknown, row: Contacte) => `${row.nom} ${row.cognoms}`,
    },
    {
      header: 'Dades contacte',
      field: 'cognoms',
      render: (_: unknown, row: Contacte) => {
        // Inicializamos un array para almacenar los elementos válidos
        const contactLinks = [];

        // Verificamos si el correo electrónico no está vacío o es nulo y lo agregamos como enlace mailto
        if (row.email && row.email !== '') {
          contactLinks.push(`<a href="mailto:${row.email}">${row.email}</a>`);
        }

        // Verificamos si el teléfono 1 no está vacío o es nulo y lo agregamos como enlace tel:
        if (row.tel_1 && row.tel_1 !== '') {
          contactLinks.push(`<a href="tel:${row.tel_1}">${row.tel_1}</a>`);
        }

        // Verificamos si el teléfono 2 no está vacío o es nulo y lo agregamos como enlace tel:
        if (row.tel_2 && row.tel_2 !== '') {
          contactLinks.push(`<a href="tel:${row.tel_2}">${row.tel_2}</a>`);
        }

        // Verificamos si el teléfono 3 no está vacío o es nulo y lo agregamos como enlace tel:
        if (row.tel_3 && row.tel_3 !== '') {
          contactLinks.push(`<a href="tel:${row.tel_3}">${row.tel_3}</a>`);
        }

        // Si hay algún dato válido en el array, los unimos con " / " y los devolvemos
        if (contactLinks.length > 0) {
          return contactLinks.join(' / ');
        } else {
          return ''; // Si no hay datos, devolvemos una cadena vacía
        }
      },
    },
    { header: 'Tipus', field: 'tipus' },
    { header: 'País', field: 'country' },
    {
      header: 'Data naixement',
      field: 'tema',
      render: (_: unknown, row: Contacte) => {
        // Verificamos si data_naixement tiene un valor válido (no null, no vacío)
        if (row.data_naixement && row.data_naixement !== null && row.data_naixement !== '') {
          return formatNaixementEdat(row.data_naixement); // Si es válido, mostramos la fecha formateada
        } else {
          return ''; // Si es null o vacío, no mostramos nada
        }
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Contacte) => `<a id="${row.idTema}" title="Modifica" href="https://${window.location.hostname}${gestioUrl}/agenda-contactes/modifica-contacte/${row.id}"><button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/contactes/get/?contactes`,
    containerId: 'taulaLlistatContactes',
    columns,
    filterKeys: ['nom', 'cognoms'],
    filterByField: 'tipus',
  });
}
