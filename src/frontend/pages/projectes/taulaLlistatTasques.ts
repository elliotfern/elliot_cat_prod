import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Tasca } from '../../types/Tasca';

export async function taulaLlistatTasques() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Tasca>[] = [
    // ------------------------------------------------
    // Projecte
    // ------------------------------------------------

    {
      header: 'Projecte',
      field: 'projecte',
      render: (_: unknown, row: Tasca) => {
        const projecte = row.projecte;

        if (!projecte) {
          return '-';
        }

        return `
          <a
            id="${row.projecte_id}"
            title="Fitxa projecte"
            href="/gestio/projectes/fitxa-projecte/${encodeURIComponent(row.projecte_id)}"
          >
            ${projecte}
          </a>
        `;
      },
    },

    // ------------------------------------------------
    // Títol
    // ------------------------------------------------

    {
      header: 'Títol',
      field: 'title',
      render: (_: unknown, row: Tasca) => {
        return row.title?.trim() || '-';
      },
    },

    // ------------------------------------------------
    // Client
    // ------------------------------------------------

    {
      header: 'Client',
      field: 'client_id',
      render: (_: unknown, row: Tasca) => {
        const nom = String(row.nom ?? '').trim();
        const cognoms = String(row.cognoms ?? '').trim();
        const empresa = String(row.empresa ?? '').trim();

        if (!row.client_id || (!nom && !cognoms && !empresa)) {
          return '-';
        }

        const nomComplet = `${nom} ${cognoms}`.trim();

        return `
          <a
            id="${row.client_id}"
            title="Fitxa client"
            href="/gestio/comptabilitat/fitxa-client/${encodeURIComponent(row.client_id)}"
          >
            ${nomComplet}
            ${empresa ? ` (${empresa})` : ''}
          </a>
        `;
      },
    },

    // ------------------------------------------------
    // Estat
    // ------------------------------------------------

    {
      header: 'Estat',
      field: 'estat',
      render: (_: unknown, row: Tasca) => {
        switch (row.estat) {
          case 'pendent':
            return '<span class="badge bg-secondary">Pendent</span>';

          case 'en_curs':
            return '<span class="badge bg-primary">En curs</span>';

          case 'finalitzat':
            return '<span class="badge bg-success">Finalitzat</span>';

          case 'arxivat':
            return '<span class="badge bg-dark">Arxivat</span>';

          default:
            return '';
        }
      },
    },

    // ------------------------------------------------
    // Prioritat
    // ------------------------------------------------

    {
      header: 'Prioritat',
      field: 'prioritat',
      render: (_: unknown, row: Tasca) => {
        switch (row.prioritat) {
          case 'baixa':
            return '<span class="badge bg-secondary">Baixa</span>';

          case 'normal':
            return '<span class="badge bg-primary">Normal</span>';

          case 'alta':
            return '<span class="badge bg-warning text-dark">Alta</span>';

          case 'urgent':
            return '<span class="badge bg-danger">Urgent</span>';

          default:
            return '';
        }
      },
    },

    // ------------------------------------------------
    // Data
    // ------------------------------------------------

    {
      header: 'Data',
      field: 'planned_date',
      render: (_: unknown, row: Tasca) => {
        const data = row.planned_date?.trim() ?? '';

        return data || '-';
      },
    },
  ];

  // ------------------------------------------------
  // Accions
  // ------------------------------------------------

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Tasca) => `
        <a
          id="${row.id}"
          title="Modifica"
          href="/gestio/projectes/modifica-tasca/${encodeURIComponent(row.id)}"
        >
          <button
            type="button"
            class="btn btn-warning btn-sm"
          >
            Modifica
          </button>
        </a>
      `,
    });
  }

  // ------------------------------------------------
  // Taula
  // ------------------------------------------------

  renderDynamicTable({
    url: 'projectes/get/llistatTasques',
    containerId: 'taulaLlistatTasques',
    columns,
    filterKeys: ['title'],
    filterByFields: ['estat'],
    filterLabels: {
      estat: 'Estat:',
    },
  });
}
