import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { API_URLS } from '../../utils/apiUrls';

interface Producte {
  id: number;
  producte: string;
  descripcio?: string;
  unitat?: string;
  preu_recomanat?: number;
  actiu: number;
}

export async function taulaLlistatProductes() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Producte>[] = [
    {
      header: 'Producte',
      field: 'producte',
      render: (_: unknown, row: Producte) =>
        `<a id="${row.id}" href="https://${window.location.hostname}/gestio/comptabilitat/fitxa-producte/${row.id}">
          ${row.producte}
        </a>`,
    },
    {
      header: 'Descripció',
      field: 'descripcio',
      render: (_: unknown, row: Producte) => `${row.descripcio ?? ''}`,
    },
    {
      header: 'Unitat',
      field: 'unitat',
      render: (_: unknown, row: Producte) => `${row.unitat ?? ''}`,
    },
    {
      header: 'Preu recomanat',
      field: 'preu_recomanat',
      render: (_: unknown, row: Producte) => (row.preu_recomanat ? `${row.preu_recomanat} €` : ''),
    },
    {
      header: 'Actiu',
      field: 'actiu',
      render: (_: unknown, row: Producte) => (row.actiu ? 'Sí' : 'No'),
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Producte) =>
        `<a id="${row.id}" title="Modifica" href="https://${window.location.hostname}/gestio/comptabilitat/modifica-producte/${row.id}">
          <button class="btn-petit">Modifica</button>
        </a>`,
    });
  }

  renderDynamicTable({
    url: API_URLS.GET.PRODUCTES,
    containerId: 'taulaLlistatProductes',
    columns,
    filterKeys: ['producte'],
  });
}
