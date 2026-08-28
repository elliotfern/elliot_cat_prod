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
      render: (_: unknown, row: Contacte) => {
        const nom = row.nom?.trim() ?? '';
        const cognoms = row.cognoms?.trim() ?? '';

        const nomComplet = `${nom} ${cognoms}`.trim();

        return nomComplet || '-';
      },
    },
    {
      header: 'Empresa',
      field: 'empresa',
      render: (_: unknown, row: Contacte) => {
        return row.empresa?.trim() || '-';
      },
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
      header: 'Estat',
      field: 'actiu',
      render: (_: unknown, row: Contacte) => {
        switch (row.actiu) {
          case '1':
            return '<span class="badge bg-success">Actiu</span>';

          case '0':
            return '<span class="badge bg-secondary">Arxivat</span>';

          default:
            return '';
        }
      },
    },
    {
      header: 'Data naixement',
      field: 'tema',
      render: (_: unknown, row: Contacte) => {
        if (row.data_naixement) {
          return formatNaixementEdat(row.data_naixement);
        } else {
          return '-';
        }
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
    filterByFields: ['tipus_persona', 'actiu'],
    filterLabels: {
      tipus_persona: 'Tipus contacte:',
      actiu: 'Estat:',
    },
  });
}
