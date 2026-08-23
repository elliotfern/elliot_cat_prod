import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatNaixementEdat } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Contacte } from '../../types/Contacte';

export async function taulaLlistatContactes() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Contacte>[] = [
    {
      header: 'Nom i cognoms',
      field: 'cognoms',
      render: (_: unknown, row: Contacte) => `${row.nom} ${row.cognoms ?? ''}`,
    },
    {
      header: 'Empresa',
      field: 'empresa',
    },
    {
      header: 'Dades contacte',
      field: 'email',
      render: (_: unknown, row: Contacte) => {
        const contactLinks = [];

        if (row.email) {
          contactLinks.push(`<a href="mailto:${row.email}">${row.email}</a>`);
        }

        if (row.tel_1) {
          contactLinks.push(`<a href="tel:${row.tel_1}">${row.tel_1}</a>`);
        }

        if (row.tel_2) {
          contactLinks.push(`<a href="tel:${row.tel_2}">${row.tel_2}</a>`);
        }

        return contactLinks.join(' / ');
      },
    },
    {
      header: 'Tipus',
      field: 'tipus_persona',
      render: (_: unknown, row: Contacte) => {
        switch (row.tipus_persona) {
          case 'FAMILIA':
            return '<span class="badge bg-success">Família</span>';

          case 'AMICS':
            return '<span class="badge bg-primary">Amics</span>';

          case 'EMPRESA':
            return '<span class="badge bg-warning text-dark">Empresa</span>';

          case 'ALTRES':
            return '<span class="badge bg-secondary">Altres</span>';

          default:
            return '';
        }
      },
    },
    {
      header: 'País',
      field: 'pais_ca',
    },
    {
      header: 'Data naixement',
      field: 'tema',
      render: (_: unknown, row: Contacte) => {
        if (row.data_naixement) {
          return formatNaixementEdat(row.data_naixement);
        }

        return '';
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Contacte) =>
        `<a id="${row.id}" title="Modifica" href="/gestio/agenda-contactes/modifica-contacte/${row.id}">
          <button type="button" class="btn btn-warning btn-sm">Modifica</button>
        </a>`,
    });
  }

  renderDynamicTable({
    url: `contactes/get/llistatContactes`,
    containerId: 'taulaLlistatContactes',
    columns,
    filterKeys: ['nom', 'cognoms', 'empresa'],
    filterByField: 'tipus_persona',
  });
}
